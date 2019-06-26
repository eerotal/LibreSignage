<?php

namespace common\php\slide;

/*
*  Slide object implementation and utility definitions.
*  The Slide object is basically the interface between the raw
*  file data and the API endpoints.
*/
use \common\php\Util;
use \common\php\Exportable;
use \common\php\auth\User;
use \common\php\Queue;
use \common\php\slide\SlideLock;
use \common\php\slide\SlideAsset;

final class SlideLockException extends \Exception {};

final class Slide extends Exportable {
	static $PUBLIC = [
		'id',
		'name',
		'index',
		'duration',
		'markup',
		'owner',
		'enabled',
		'sched',
		'sched_t_s',
		'sched_t_e',
		'animation',
		'queue_name',
		'collaborators',
		'lock',
		'assets'
	];

	static $PRIVATE = [
		'id',
		'name',
		'index',
		'duration',
		'markup',
		'owner',
		'enabled',
		'sched',
		'sched_t_s',
		'sched_t_e',
		'animation',
		'queue_name',
		'collaborators',
		'lock',
		'assets'
	];

	// Slide data variables.
	private $id = NULL;
	private $name = NULL;
	private $index = NULL;
	private $duration = NULL;
	private $markup = NULL;
	private $owner = NULL;
	private $enabled = FALSE;
	private $sched = FALSE;
	private $sched_t_s = 0;
	private $sched_t_e = 0;
	private $animation = 0;
	private $queue_name = NULL;
	private $collaborators = [];
	private $lock = NULL;
	private $assets = [];

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	/**
	* Load a slide from file.
	*
	* @param string $id The ID of the slide to load.
	* @throws ArgException if a slide with ID $id doesn't exist.
	*/
	public function load(string $id) {
		$tmp = NULL;

		$this->set_id($id);
		if (!is_file($this->get_conf_path())) {
			throw new ArgException("Slide $id doesn't exist.");
		}

		$tmp = Util::file_lock_and_get($this->get_conf_path());
		$this->import(JSONUtil::decode($tmp), TRUE);

		$this->lock_cleanup();
		$this->check_sched_enabled();
	}

	/**
	* Duplicate the loaded slide.
	*
	* @return Slide The duplicate slide.
	*/
	public function dup(): Slide {
		$slide = clone $this;
		$slide->gen_id();
		$slide->set_index($slide->get_index() + 1);
		$slide->set_lock(NULL);

		// Make sure all directories are created.
		$slide->write();

		$tmp = [];
		foreach ($this->get_assets() as $a) {
			$tmp[] = $a->clone($slide);
		}
		$slide->set_assets($tmp);

		// Write latest changes to file.
		$slide->write();

		$queue = $slide->get_queue();
		$queue->add($slide);
		$queue->write();

		return $slide;
	}

	/**
	* Generate a new ID for the loaded slide.
	*/
	private function gen_id() {
		$this->id = get_uid();
	}

	/**
	* Set the slide id. Note that the requested slide
	* ID must already exist. Otherwise an error is
	* thrown. This basically means that new slide IDs
	* can't be set manually and they are always generated
	* by the server.
	*
	* @param string $id The ID to set.
	* @throws ArgException if the slide ID $id doesn't already exist.
	*/
	public function set_id(string $id) {
		if (!in_array($id, self::list_ids())) {
			throw new ArgException("Slide $id doesn't exist.");
		}
		$this->id = $id;
	}

	/**
	* Set the slide markup.
	*
	* @param string $markup The slide markup.
	* @throws ArgException if the markup is longer that SLIDE_MARKUP_MAX_LEN chars. 
	*/
	public function set_markup(string $markup) {
		// Check markup length.
		if (strlen($markup) > Config::limit('SLIDE_MARKUP_MAX_LEN')) {
			throw new ArgException("Slide markup too long.");
		}
		$this->markup = $markup;
	}

	/**
	* Set the slide name.
	*
	* @param string $name The slide name.
	* @throws ArgException if the slide name contains invalid characters.
	* @throws IntException if preg_match() fails.
	* @throws ArgException if the slide name is empty.
	* @throes ArgException if the slide name is longer than SLIDE_NAME_MAX_LEN chars.
	*/
	public function set_name(string $name) {
		$tmp = preg_match('/[^a-zA-Z0-9_-]/', $name);
		if ($tmp) {
			throw new ArgException("Invalid chars in slide name.");
		} else if ($tmp === NULL) {
			throw new IntException("preg_match() match failed.");
		}

		if (strlen($name) === 0) {
			throw new ArgException("Invalid empty slide name.");
		} else if (strlen($name) > Config::limit('SLIDE_NAME_MAX_LEN')) {
			throw new ArgException("Slide name too long.");
		}
		$this->name = $name;
	}

	/**
	* Set the slide index.
	*
	* @param int $index The slide index.
	* @throws ArgException if $index < 0 or $index > SLIDE_MAX_INDEX.
	*/
	public function set_index(int $index) {
		if ($index < 0 || $index > Config::limit('SLIDE_MAX_INDEX')) {
			throw new ArgException("Slide index $index out of bounds.");
		}
		$this->index = $index;
	}

	/**
	* Set the slide duration.
	*
	* @param int $duration The slide duration in seconds.
	* @throws ArgException if $duration < SLIDE_MIN_DURATION or
	*                      $duration > SLIDE_MAX_DURATION.
	*/
	public function set_duration(int $duration) {
		// Check duration bounds.
		if (
			$duration < Config::limit('SLIDE_MIN_DURATION')
			|| $duration > Config::limit('SLIDE_MAX_DURATION')
		) {
			throw new ArgException("Slide duration $duration out of bounds.");
		}
		$this->duration = $duration;
	}

	/**
	* Set the slide owner.
	*
	* @param string $owner The slide owner.
	* @throws ArgException if the user $owner doesn't exist.
	*/
	public function set_owner(string $owner) {
		if (!User::exists($owner)) {
			throw new ArgException("User $owner doesn't exist.");
		}
		$this->owner = $owner;
	}

	/**
	* Set whether the slide is enabled or not.
	*
	* @param bool $enabled TRUE = Enabled, FALSE = Disabled.
	*/
	public function set_enabled(bool $enabled) {
		$this->enabled = $enabled;
	}

	/**
	* Set whether the slide is scheduled.
	*
	* @param bool $sched TRUE = Scheduled, FALSE = Normal.
	*/
	public function set_sched(bool $sched) {
		$this->sched = $sched;
	}

	/**
	* Set the slide schedule start time.
	*
	* @param int $tstamp The starting timestamp in seconds.
	* @throws ArgException if $tstamp < 0.
	*/
	public function set_sched_t_s(int $tstamp) {
		if ($tstamp < 0) {
			throw new ArgException(
				"Invalid negative schedule start timestamp."
			);
		}
		$this->sched_t_s = $tstamp;
	}

	/**
	* Set the slide schedule end time.
	*
	* @param int $tstamp The ending timestamp in seconds.
	* @throws ArgException if $tstamp < 0.
	*/
	public function set_sched_t_e(int $tstamp) {
		if ($tstamp < 0) {
			throw new ArgException(
				"Invalid negative schedule end timestamp."
			);
		}
		$this->sched_t_e = $tstamp;
	}

	/**
	* Set the slide animation ID.
	*
	* @param int $anim The ID of the slide animation.
	* @throws ArgException if $anim < 0.
	*/
	public function set_animation(int $anim) {
		if ($anim < 0) {
			throw new ArgException("Invalid negative animation.");
		}
		$this->animation = $anim;
	}

	/**
	* Set the slide queue.
	*
	* @param string $name The queue name.
	*/
	public function set_queue(string $name) {
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

	/**
	* Set the slide collaborators.
	*
	* @param array $collaborators An array of collaborator usernames.
	* @throws ArgException if count($collaborators) > SLIDE_MAX_COLLAB.
	* @throws ArgException if the slide owner is not set.
	* @throws ArgException if the slide owner is a collaborator.
	* @throws ArgException if a collaborator doesn't exist.
	*/
	function set_collaborators(array $collaborators) {
		if (count($collaborators) > Config::limit('SLIDE_MAX_COLLAB')) {
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
			if (!User::exists($c)) {
				throw new ArgException("User $c doesn't exist.");
			}
		}
		$this->collaborators = $collaborators;
	}

	/**
	* Set the slide lock object.
	*
	* @param mixed $lock The SlideLock object.
	*/
	public function set_lock($lock) {
		$this->lock = $lock;
	}

	/**
	* Cleanup expired slide locks.
	*/
	private function lock_cleanup() {
		if (
			$this->lock !== NULL
			&& $this->lock->is_expired()
		) {
			$this->lock = NULL;
		}
		$this->write();
	}

	/**
	* Attempt to lock this slide.
	*
	* @param Session $session The Session to lock the slide for.
	* @throws SlideLockException if the slide is already
	*                            locked by another user.
	*/
	public function lock_acquire(Session $session) {
		if (
			$this->lock !== NULL
			&& !$this->lock->is_owned_by($session)
		) {
			throw new SlideLockException("Slide already locked.");
		}
		$this->lock = new SlideLock($session);
	}

	/**
	* Attempt to unlock this slide.
	*
	* @param Session $session The Session object of the caller.
	* @throws SlideLockException if the slide is locked by another user.
	*/
	public function lock_release(Session $session) {
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

	/**
	* Store an uploaded asset in the 'assets' directory of this
	* slide. This function also generates a thumbnail for the asset.
	*
	* @param array $file The upload data from $_FILE.
	* @throws LimitException if the asset limit SLIDE_MAX_ASSETS is reached.
	*/
	public function store_uploaded_asset(array $file) {
		$this->assert_ready();

		if (
			!empty($this->assets)
			&& count($this->assets) >= Config::limit('SLIDE_MAX_ASSETS')
		) {
			throw new LimitException('Too many slide assets.');
		}
		if (!is_dir($this->get_asset_path())) { mkdir($this->get_asset_path()); }
		$asset = new SlideAsset();
		$asset->new($file, $this);
		$this->assets[] = $asset;
	}

	/**
	* Remove an uploaded asset from the 'assets' directory of this slide.
	*
	* @param string $name The name of the asset to remove.
	* @throws ArgException if the asset $name doesn't exist.
	*/
	public function remove_uploaded_asset(string $name) {
		$this->assert_ready();
		for ($i = 0; $i < count($this->assets); $i++) {
			if ($this->assets[$i]->get_filename() === $name) {
				$this->assets[$i]->remove();
				unset($this->assets[$i]);
				$this->assets = array_values($this->assets);
				return;
			}
		}
		throw new ArgException("Asset '$name' doesn't exist.");
	}

	/**
	* Get the SlideAsset named $name.
	*
	* @param string $name The name of the asset.
	* @return SlideAsset|NULL The requested SlideAsset or NULL
	*                         if it doesn't exist.
	*/
	public function get_uploaded_asset(string $name) {
		if (empty($this->assets)) { return NULL; }
		foreach ($this->assets as $a) {
			if ($a->get_filename() === $name) {
				return $a;
			}
		}
		return NULL;
	}

	public function has_uploaded_asset(string $name) {
		return $this->get_uploaded_asset($name) !== NULL;
	}

	public function set_assets(array $assets) {
		$this->assets = $assets;
	}

	/**
	* Check whether the slide is enabled based on
	* the scheduling config. This function basically
	* overrides the manual 'enabled' control.
	*/
	public function check_sched_enabled() {
		$t = time();
		if (
			$this->get_sched()
			&& $t >= $this->get_sched_t_s()
			&& $t <= $this->get_sched_t_e()
		) {
			// Scheduling active -> enable.
			$this->set_enabled(TRUE);
			$this->write();
		} else if ($this->get_sched()){
			// Scheduling inactive -> disable.
			$this->set_enabled(FALSE);
			$this->write();
		}
	}

	/**
	* Check whether $user can modify the loaded slide.
	*
	* @param User $user The user to check.
	* @return bool TRUE = Can modify, FALSE = Can't modify.
	*/
	function can_modify(User $user): bool {
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

	/**
	* Throw an exception if the slide is not ready. Use
	* this in functions that access files to prevent trying
	* to access nonexistent files.
	*
	* @throws IntException if the slide is not ready.
	*/
	private function assert_ready() {
		assert(
			!empty($this->id),
			IntException('Slide not ready.')
		);
	}

	/**
	* Write slide data to file.
	*/
	public function write() {
		$tmp = '';

		// Generate an ID for unsaved slides.
		if (empty($this->id)) {
			$this->gen_id();
		}

		$this->assert_ready();

		if (!is_dir($this->get_dir_path())) {
			mkdir($this->get_dir_path());
		}
		if (!is_dir($this->get_asset_path())) {
			mkdir($this->get_asset_path());
		}

		$tmp = JSONUtil::encode($this->export(TRUE, TRUE));
		Util::file_lock_and_put($this->get_conf_path(), $tmp);
	}

	/**
	* Remove the loaded slide from file.
	*/
	public function remove() {
		$this->assert_ready();

		// Remove slide from its queue.
		$queue = $this->get_queue();
		$queue->remove_slide($this);
		$queue->write();

		// Remove slide data files.
		Util::rmdir_recursive($this->get_dir_path());
	}

	private function get_dir_path(): string {
		assert(!empty($this->id), new IntException("Slide ID can't be empty."));
		return LIBRESIGNAGE_ROOT.SLIDES_DIR.'/'.$this->id;
	}

	private function get_conf_path(): string {
		return $this->get_dir_path().'/conf.json';	
	}

	private function get_asset_path(): string {
		return $this->get_dir_path().'/assets';	
	}

	public function get_id() { return $this->id; }
	public function get_markup() { return $this->markup; }
	public function get_name() { return $this->name; }
	public function get_index() { return $this->index; }
	public function get_duration() { return $this->duration; }
	public function get_owner() { return $this->owner; }
	public function get_enabled() { return $this->enabled; }
	public function get_sched() { return $this->sched; }
	public function get_sched_t_s() { return $this->sched_t_s; }
	public function get_sched_t_e() { return $this->sched_t_e; }
	public function get_animation() { return $this->animation; }
	public function get_queue_name() { return $this->queue_name; }
	public function get_collaborators() { return $this->collaborators; }
	public function get_lock() { return $this->lock; }
	public function get_assets() { return $this->assets; }

	public function get_queue(): Queue {
		$queue = new Queue($this->queue_name);
		$queue->load();
		return $queue;
	}

	/**
	* List all existing Slide IDs.
	*
	* @return array An array of Slide IDs.
	*/
	public static function list_ids(): array {
		$ids = scandir(LIBRESIGNAGE_ROOT.SLIDES_DIR);

		// Remove '.', '..' and hidden files.
		return array_filter($ids, function(string $val) {
			return substr($val, 0, 1) != '.';
		});
	}

	/**
	* List all Slide objects.
	*
	* @return array An array of Slide objects.
	*/
	public static function list(): array {
		$ids = self::list_ids();
		$slides = [];
		$tmp = NULL;

		foreach ($ids as $id) {
			$tmp = new Slide();
			$tmp->load($id);
			$slides[] = $tmp;
		}
		return $slides;
	}
}

