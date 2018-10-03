<?php

/*
*  Slide object implementation and utility definitions.
*  The Slide object is basically the interface between the raw
*  file data and the API endpoints.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slidelock.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/uid.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/queue.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/exportable/exportable.php');

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

class SlideLockException extends Exception {};

class Slide extends Exportable{
	static $PUBLIC = [
		'id',
		'name',
		'index',
		'time',
		'markup',
		'owner',
		'enabled',
		'sched',
		'sched_t_s',
		'sched_t_e',
		'animation',
		'queue_name',
		'collaborators',
		'lock'
	];

	static $PRIVATE = [
		'id',
		'name',
		'index',
		'time',
		'markup',
		'owner',
		'enabled',
		'sched',
		'sched_t_s',
		'sched_t_e',
		'animation',
		'queue_name',
		'collaborators',
		'lock'
	];

	// Slide file paths.
	private $conf_path = NULL;
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
	private $queue_name = NULL;
	private $collaborators = NULL;
	private $lock = NULL;

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	private function _mk_paths(string $id) {
		/*
		*  Create the file path strings needed for
		*  data storage.
		*/
		$this->dir_path = LIBRESIGNAGE_ROOT.SLIDES_DIR.'/'.$id;
		$this->conf_path = $this->dir_path.'/conf.json';
	}

	private function _paths_exist() {
		/*
		*  Check that all the required files and
		*  directories exist.
		*/
		if (
			!is_dir($this->dir_path)
			|| !is_file($this->conf_path)
		) {
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
			throw new ArgException("Slide $id doesn't exist.");
		}

		// Read config.
		$cstr = file_lock_and_get($this->conf_path);
		if ($cstr === FALSE) {
			throw new IntException("Slide config read error!");
		}
		$conf = json_decode($cstr, $assoc=TRUE);
		if (
			$conf === NULL
			&& json_last_error() != JSON_ERROR_NONE
		) { throw new IntException("Slide config decode error!"); }

		$this->import($conf, TRUE);
		$this->check_sched_enabled();
	}

	function dup() {
		/*
		*  Duplicate a slide.
		*/
		$slide = clone $this;
		$slide->gen_id();
		$slide->set_index($slide->get_index() + 1);
		$slide->set_lock(NULL);
		$slide->write();

		$queue = $slide->get_queue();
		$queue->add($slide);
		$queue->write();

		return $slide;
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
			throw new ArgException("Slide $id doesn't exist.");
		}
		$this->id = $id;
		$this->_mk_paths($id);
	}

	function set_markup(string $markup) {
		// Check markup length.
		if (strlen($markup) > gtlim('SLIDE_MARKUP_MAX_LEN')) {
			throw new ArgException("Slide markup too long.");
		}
		$this->markup = $markup;
	}

	function set_name(string $name) {
		// Check name for invalid chars.
		$tmp = preg_match('/[^a-zA-Z0-9_-]/', $name);
		if ($tmp) {
			throw new ArgException("Invalid chars in slide name.");
		} else if ($tmp === NULL) {
			throw new IntException("Regex match failed.");
		}

		// Check name length.
		if (strlen($name) > gtlim('SLIDE_NAME_MAX_LEN')) {
			throw new ArgException("Slide name too long.");
		}
		$this->name = $name;
	}

	function set_index(int $index) {
		// Check index bounds.
		if ($index < 0 || $index > gtlim('SLIDE_MAX_INDEX')) {
			throw new ArgException("Slide index $index out of bounds.");
		}
		$this->index = $index;
	}

	function set_time(int $time) {
		// Check time bounds.
		if (
			$time < gtlim('SLIDE_MIN_TIME')
			|| $time > gtlim('SLIDE_MAX_TIME')
		) {
			throw new ArgException("Slide time $time out of bounds.");
		}
		$this->time = $time;
	}

	function set_owner(string $owner) {
		if (!user_exists($owner)) {
			throw new ArgException("User $owner doesn't exist.");
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
				"Invalid negative schedule ".
				"start timestamp."
			);
		}
		$this->sched_t_s = $tstamp;
	}

	function set_sched_t_e(int $tstamp) {
		if ($tstamp < 0) {
			throw new ArgException(
				"Invalid negative schedule ".
				"end timestamp."
			);
		}
		$this->sched_t_e = $tstamp;
	}

	function set_animation(int $anim) {
		if ($anim < 0) {
			throw new ArgException("Invalid negative animation.");
		}
		$this->animation = $anim;
	}

	function set_queue(string $name) {
		if ($this->queue_name != $name) {
			if ($this->queue_name) {
				// Remove slide from the old queue.
				$o = $this->get_queue();
				$o->remove_slide($this);
				$o->write();
			}

			// Add slide to the the new queue.
			$this->queue_name = $name;
			$n = new Queue($name);
			$n->load();
			$n->add($this);
			$n->write();
		}
	}

	private function set_queue_name(string $name) {
		$this->queue_name = $name;
	}

	function set_collaborators(array $collaborators) {
		if (count($collaborators) > gtlim('SLIDE_MAX_COLLAB')) {
			throw new ArgException( "Too many collaborators.");
		}
		if (empty($this->get_owner())) {
			throw new ArgException(
				"Can't set collaborators before owner."
			);
		}
		foreach ($collaborators as $k => $c) {
			if ($c == $this->get_owner()) {
				throw new ArgException(
					"Can't set owner as collaborator."
				);
			}
			if (!user_exists($c)) {
				throw new ArgException("User $c doesn't exist.");
			}
		}
		$this->collaborators = $collaborators;
	}

	public function set_lock($lock) {
		$this->lock = $lock;
	}

	private function lock_cleanup() {
		/*
		*  Cleanup expired slide locks.
		*/
		if (
			$this->lock !== NULL
			&& $this->lock->is_expired()
		) {
			$this->lock = NULL;
		}
	}

	function lock_acquire(Session $session) {
		/*
		*  Attempt to lock this slide. Throws a SlideLockException
		*  if the slide is already locked by another user.
		*/
		$this->lock_cleanup();
		if (
			$this->lock !== NULL
			&& !$this->lock->is_owned_by($session)
		) {
			throw new SlideLockException("Slide already locked.");
		}
		$this->lock = new SlideLock($session);
	}

	function lock_release(Session $session) {
		/*
		*  Attempt to unlock this slide. Throws a SlideLockException
		*  if the slide is locked by another user.
		*/
		$this->lock_cleanup();
		if (
			$this->lock !== NULL
			&& !$this->lock->is_owned_by($session)
		) {
			throw new SlideLockException(
				"Can't unlock a slide locked from another session."
			);
		}
		$this->lock = NULL;
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
	function get_queue_name() { return $this->queue_name; }
	function get_collaborators() { return $this->collaborators; }
	function get_lock() { return $this->lock; }

	function get_queue() {
		$queue = new Queue($this->queue_name);
		$queue->load();
		return $queue;
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

	function can_modify(User $user) {
		return (
			$user->is_in_group('admin')
			|| $this->get_owner() === $user->get_name()
			|| in_array(
				$user->get_name(),
				$this->get_collaborators(),
				TRUE
			)
		);
	}

	function write() {
		/*
		*  Write the currently stored data into the correct
		*  storage files. This function  overwrites files if
		*  they already exist.
		*/
		$cstr = json_encode($this->export(TRUE, TRUE));
		if (
			$cstr === FALSE &&
			json_last_error() != JSON_ERROR_NONE
		) { throw new IntException("Slide config encoding failed."); }
		file_lock_and_put($this->conf_path, $cstr);
	}

	function remove() {
		/*
		*  Remove this slide.
		*/

		// Remove slide from its queue.
		$queue = $this->get_queue();
		$queue->remove_slide($this);
		$queue->write();

		// Remove slide data files.
		if (!empty($this->dir_path)) {
			rmdir_recursive($this->dir_path);
		}
	}
}

