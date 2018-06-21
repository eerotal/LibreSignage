<?php

/*
*  Slide object implementation and utility definitions.
*  The Slide object is basically the interface between the raw
*  file data and the API endpoints.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/uid.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

function slides_id_list() {
	$ids = scandir(LIBRESIGNAGE_ROOT.SLIDES_DIR);

	// Remove '.', '..' and hidden files.
	return array_filter($ids, function(string $val) {
		return substr($val, 0, 1) != '.';
	});
}

function slides_list() {
	$ids = slides_id_list();
	$slides = array();
	$tmp = NULL;

	foreach ($ids as $id) {
		$tmp = new Slide();
		$tmp->load($id);
		$slides[] = $tmp;
	}
	return $slides;
}

function sort_slides_by_index(array &$slides) {
	/*
	*  Sort the slides in $slides by their indices.
	*/
	usort($slides, function(Slide $a, Slide $b) {
		if ($a->get_index() > $b->get_index()) {
			return 1;
		} else if ($a->get_index() < $b->get_index()) {
			return -1;
		} else {
			return 0;
		}
	});
}

function normalize_slide_indices(array &$slides) {
	/*
	*  Normalize and sort the slide array $slides.
	*/
	sort_slides_by_index($slides);
	for ($i = 0; $i < count($slides); $i++) {
		$slides[$i]->set_index($i);
		$slides[$i]->write();
	}
}

function juggle_slide_indices(string $keep_id = "") {
	/*
	*  Recalculate slide indices so that the position of the
	*  slide with the id $keep_id stays the same, no unused
	*  indices remain and slides are sorted based on the
	*  indices. If $keep_id is empty, the indices are just
	*  normalized and sorted.
	*/

	$slides = slides_list();
	$keep = NULL;
	$clash = FALSE;

	// Remove the the slide with ID $keep_id initially.
	if (!empty($keep_id)) {
		foreach ($slides as $k => $s) {
			if ($s->get_id() == $keep_id) {
				$keep = $s;
				unset($slides[$k]);
				$slides = array_values($slides);
				break;
			}
		}
	}

	normalize_slide_indices($slides);
	if (!empty($keep_id)) {
		/*
		*  Shift indices so that the index of $keep_id is
		*  left free.
		*/
		foreach ($slides as $k => $s) {
			$clash |= $s->get_index() == $keep->get_index();
			if ($s->get_index() >= $keep->get_index()) {
				$s->set_index($s->get_index() + 1);
				$s->write();
			}
		}
		if (!$clash) {
			/*
			*  $keep_id didn't have the same index as any of
			*  the other slides -> make it the last one.
			*/
			$keep->set_index(count($slides));
			$keep->write();
		}
	}
}

class Slide {
	// Required keys in a slide config file.
	const CONF_KEYS = array(
		'name',
		'index',
		'time',
		'owner',
		'enabled',
		'sched',
		'sched_t_s',
		'sched_t_e',
		'animation'
	);

	// Slide file paths.
	private $conf_path = NULL;
	private $markup_path = NULL;
	private $dir_path = NULL;

	// Slide data variables.
	private $id = NULL;
	private $name = NULL;
	private $index = NULL;
	private $time = NULL;
	private $markup = NULL;
	private $owner = NULL;
	private $enabled = FALSE;
	private $sched = FALSE;
	private $sched_t_s = 0;
	private $sched_t_e = 0;
	private $animation = 0;

	private function _mk_paths(string $id) {
		/*
		*  Create the file path strings needed for
		*  data storage.
		*/
		$this->dir_path = LIBRESIGNAGE_ROOT.SLIDES_DIR.'/'.$id;
		$this->conf_path = $this->dir_path.'/conf.json';
		$this->markup_path = $this->dir_path.'/markup.dat';
	}

	private function _paths_exist() {
		/*
		*  Check that all the required files and
		*  directories exist.
		*/
		if (!is_dir($this->dir_path) ||
			!is_file($this->conf_path) ||
			!is_file($this->markup_path)) {
			return FALSE;
		}
		return TRUE;
	}

	function load(string $id) {
		/*
		*  Load the decoded data of a slide. This
		*  function throws errors on exceptions.
		*/
		$cstr = NULL;
		$conf = NULL;
		$mu = NULL;

		$this->_mk_paths($id);
		if (!$this->_paths_exist()) {
			return FALSE;
		}

		// Read config.
		$cstr = file_lock_and_get($this->conf_path);
		if ($cstr === FALSE) {
			throw new IntException(
				"Slide config read error!"
			);
		}
		$conf = json_decode($cstr, $assoc=TRUE);
		if ($conf === NULL &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new IntException(
				"Slide config decode error!"
			);
		}

		// Check config validity.
		if (!array_is_equal(array_keys($conf),
					self::CONF_KEYS)) {
			var_dump(array_keys($conf));
			throw new IntException(
				"Invalid slide config."
			);
		}

		// Read markup.
		$mu = file_lock_and_get($this->markup_path);
		if ($mu === FALSE) {
			throw new IntException(
				"Slide markup read error!"
			);
		}

		// Copy all loaded data to this object.
		$this->set_id($id);
		$this->set_markup($mu);
		$this->set_name($conf['name']);
		$this->set_index($conf['index']);
		$this->set_time($conf['time']);
		$this->set_owner($conf['owner']);
		$this->set_enabled($conf['enabled']);
		$this->set_sched($conf['sched']);
		$this->set_sched_t_s($conf['sched_t_s']);
		$this->set_sched_t_e($conf['sched_t_e']);
		$this->set_animation($conf['animation']);

		$this->check_sched_enabled();

		return TRUE;
	}

	function gen_id() {
		/*
		*  Generate a new slide ID.
		*/
		$this->id = get_uid();
		$this->_mk_paths($this->id);
	}

	function set_id(string $id) {
		/*
		*  Set the slide id. Note that the requested slide
		*  ID must already exist. Otherwise an error is
		*  thrown. This basically means that new slide IDs
		*  can't be set manually and they are always randomly
		*  generated.
		*/
		if (!in_array($id, slides_id_list())) {
			throw new ArgException(
				"Slide $id doesn't exist."
			);
		}
		$this->id = $id;
		$this->_mk_paths($id);
	}

	function set_markup(string $markup) {
		// Check markup length.
		if (strlen($markup) > gtlim('SLIDE_MARKUP_MAX_LEN')) {
			throw new ArgException(
				"Slide markup too long."
			);
		}
		$this->markup = $markup;
	}

	function set_name(string $name) {
		// Check name for invalid chars.
		$tmp = preg_match('/[^a-zA-Z0-9_-]/', $name);
		if ($tmp) {
			throw new ArgException(
				"Invalid chars in slide name."
			);
		} else if ($tmp === NULL) {
			throw new IntException(
				"Regex match failed."
			);
		}

		// Check name length.
		if (strlen($name) > gtlim('SLIDE_NAME_MAX_LEN')) {
			throw new ArgException(
				"Slide name too long."
			);
		}
		$this->name = $name;
	}

	function set_index(int $index) {
		// Check index bounds.
		if ($index < 0 || $index > gtlim('SLIDE_MAX_INDEX')) {
			throw new ArgException(
				"Slide index $index out of bounds."
			);
		}
		$this->index = $index;
	}

	function set_time(int $time) {
		// Check time bounds.
		if ($time < gtlim('SLIDE_MIN_TIME') ||
			$time > gtlim('SLIDE_MAX_TIME')) {
			throw new ArgException(
				"Slide time $time out of bounds."
			);
		}
		$this->time = $time;
	}

	function set_owner(string $owner) {
		if (!user_exists($owner)) {
			throw new ArgException(
				"User $owner doesn't exist."
			);
		}
		$this->owner = $owner;
	}

	function set_enabled(bool $enabled) {
		$this->enabled = $enabled;
	}

	function set_sched(bool $sched) {
		$this->sched = $sched;
	}

	function set_sched_t_s(int $tstamp) {
		if ($tstamp < 0) {
			throw new ArgException(
				"Invalid negative schedule start timestamp."
			);
		}
		$this->sched_t_s = $tstamp;
	}

	function set_sched_t_e(int $tstamp) {
		if ($tstamp < 0) {
			throw new ArgException(
				"Invalid negative schedule end timestamp."
			);
		}
		$this->sched_t_e = $tstamp;
	}

	function set_animation(int $anim) {
		if ($anim < 0) {
			throw new ArgException(
				"Invalid negative animation."
			);
		}
		$this->animation = $anim;
	}

	function get_id() { return $this->id; }
	function get_markup() { return $this->markup; }
	function get_name() { return $this->name; }
	function get_index() { return $this->index; }
	function get_time() { return $this->time; }
	function get_owner() { return $this->owner; }
	function get_enabled() { return $this->enabled; }
	function get_sched() { return $this->sched; }
	function get_sched_t_s() { return $this->sched_t_s; }
	function get_sched_t_e() { return $this->sched_t_e; }
	function get_animation() { return $this->animation; }

	function get_data_array() {
		return array(
			'id' => $this->id,
			'markup' => $this->markup,
			'name' => $this->name,
			'index' => $this->index,
			'time' => $this->time,
			'owner' => $this->owner,
			'enabled' => $this->enabled,
			'sched' => $this->sched,
			'sched_t_s' => $this->sched_t_s,
			'sched_t_e' => $this->sched_t_e,
			'animation' => $this->animation
		);
	}

	function check_sched_enabled() {
		/*
		*  Check whether the slide is enabled based on
		*  the scheduling config. This function basically
		*  overrides the manual 'enabled' control.
		*/
		$t = time();
		if ($this->get_sched() &&
			$t >= $this->get_sched_t_s() &&
			$t <= $this->get_sched_t_e()) {

			// Scheduling active -> enable.
			$this->set_enabled(TRUE);
			$this->write();
		} else if ($this->get_sched()){
			// Scheduling inactive -> disable.
			$this->set_enabled(FALSE);
			$this->write();
		}
	}

	function write() {
		/*
		*  Write the currently stored data into the
		*  correct storage files. This function
		*  automatically overwrites files if they
		*  already exist.
		*/
		$conf = $this->get_data_array();
		unset($conf['id']);
		unset($conf['markup']);

		$cstr = json_encode($conf);
		if ($cstr === FALSE &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new IntException(
				"Slide config encoding failed."
			);
		}
		file_lock_and_put(
			$this->conf_path,
			$cstr
		);
		file_lock_and_put(
			$this->markup_path,
			$this->get_markup()
		);
	}

	function remove() {
		/*
		*  Remove the files associated with this slide.
		*/
		if (!empty($this->dir_path)) {
			rmdir_recursive($this->dir_path);
		}
	}
}

