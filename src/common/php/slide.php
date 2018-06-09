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

function get_slides_id_list() {
	$slides_dir_abs = LIBRESIGNAGE_ROOT.SLIDES_DIR;
	$slide_ids = scandir($slides_dir_abs);
	$slide_ids = array_values(array_diff($slide_ids,
					array('.', '..')));
	$i = 0;
	while ($i < count($slide_ids)) {
		if (substr($slide_ids[$i], 0, 1) == ".") {
			array_splice($slide_ids, $i, 1);
			continue;
		}
		$slide_ids[$i] = $slide_ids[$i];
		$i++;
	}
	return $slide_ids;
}

function get_slides_list() {
	$slide_ids = get_slides_id_list();
	$slides = array();

	for ($i = 0; $i < count($slide_ids); $i++) {
		$slides[$i] = new Slide();
		$slides[$i]->load($slide_ids[$i]);
	}
	return $slides;
}

function sort_slides_by_index(array &$slides) {
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
	*  Sort the slide array $slides and recalculate
	*  the slide indices so that no unused indices remain.
	*  This function works on the $slides array reference
	*  and doesn't return any value.
	*/
	sort_slides_by_index($slides);
	for ($i = 0; $i < count($slides); $i++) {
		$s = $slides[$i];
		$s->set_index($i);
	}
}

function juggle_slide_indices(string $force_index_for) {
	/*
	*  Recalculate slide indices and preserve the apparent
	*  index of the slide with the ID $force_index_for but
	*  do it so that no unused indices remain.
	*  If $force_index_for is not set, the slide indices
	*  are just recalculated so that no unused indices remain.
	*  Below is an illustration of how this algorithm works.
	*  The lines represent arrays of slide indices.
	*
	*  F = forced index (= eg. 3)
	*  U = unused index.
	*
	*  -> Normalize and sort slide array based on indices.
	*
	*  1    2    3    4    5    6    7
	*
	*  -> Increment indices starting from F.
	*
	*  1    2    U    3+1  4+1  5+1  6+1  7+1
	*  1    2    U    4    5    6    7    8
	*
	*  -> U = F
	*
	*  1    2    F    4    5    6    7    8
	*  1    2    3    4    5    6    7    8
	*/
	$unused = -1;
	$forced = NULL;
	$slides = get_slides_list();

	// Store the forced slide separately.
	for ($i = 0; $i < count($slides); $i++) {
		if ($slides[$i]->get_id() == $force_index_for) {
			$forced = $slides[$i];
			unset($slides[$i]);
			$slides = array_values($slides);
			break;
		}
	}

	normalize_slide_indices($slides);

	if ($forced) {
		/*
		*  Recalculate the slide indices including the
		*  $forced slide in the calculations. Note that
		*  this part depends on the assumption that $slides
		*  is sorted, which is done by normalize_slide_indices().
		*
		*/
		$s_i = 0;
		$f_i = 0;
		foreach ($slides as $s) {
			$s_i = $s->get_index();
			$f_i = $forced->get_index();
			if ($s_i >= $f_i) {
				// Advance indices after $forced by one.
				$s->set_index($s_i + 1);
			}
			if ($s_i == $f_i && $unused == -1) {
				// Store the unused index.
				$unused = $s_i;
			}
			$s->write();
		}
		if ($unused == -1) {
			/*
			*  If the unused index is not set at this point,
			*  it means $forced should have the last index,
			*  which is $s_i + 1.
			*/
			$unused = $s_i + 1;
		}
		$forced->set_index($unused);
		$forced->write();
	}
}

class Slide {
	// Required keys in a slide config file.
	const CONF_KEYS = array(
		'name',
		'index',
		'time',
		'owner'
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
		if (!in_array($id, get_slides_id_list())) {
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

	function get_id() { return $this->id; }
	function get_markup() { return $this->markup; }
	function get_name() { return $this->name; }
	function get_index() { return $this->index; }
	function get_time() { return $this->time; }
	function get_owner() { return $this->owner; }

	function get_data_array() {
		return array(
			'id' => $this->id,
			'markup' => $this->markup,
			'name' => $this->name,
			'index' => $this->index,
			'time' => $this->time,
			'owner' => $this->owner
		);
	}

	function write() {
		/*
		*  Write the currently stored data into the
		*  correct storage files. This function
		*  automatically overwrites files if they
		*  already exist.
		*/
		$conf = array(
			'name' => $this->get_name(),
			'index' => $this->get_index(),
			'time' => $this->get_time(),
			'owner' => $this->get_owner()
		);

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

