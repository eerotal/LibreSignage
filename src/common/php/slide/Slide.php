<?php

namespace libresignage\common\php\slide;

use libresignage\common\php\Config;
use libresignage\common\php\Util;
use libresignage\common\php\JSONUtils;
use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\auth\User;
use libresignage\common\php\auth\Session;
use libresignage\common\php\queue\Queue;
use libresignage\common\php\slide\SlideLock;
use libresignage\common\php\slide\SlideAsset;
use libresignage\common\php\slide\exceptions\SlideLockException;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;
use libresignage\common\php\Log;
use libresignage\common\php\exceptions\ArgException;
use libresignage\common\php\exceptions\IntException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class Slide extends Exportable {
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

	public function __exportable_write() {
		$this->write();
	}

	public static function __exportable_private(): array {
		return [
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
	}

	public static function __exportable_public(): array {
		return [
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
	}

	/**
	* Load a slide from file.
	*
	* @see Slide::validate_id() for validation exceptions.
	*
	* @param string $id The ID of the slide to load.
	*/
	public function load(string $id) {
		self::validate_id($id);

		$this->fimport(self::get_conf_path($id));
		$this->lock_cleanup();
		$this->update_sched_enabled();
		$this->write();
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
	* Generate a new ID for a slide.
	*/
	public function gen_id() {
		$this->id = Util::get_uid();
	}

	/**
	* Validate the slide ID $id. This function checks that
	* $id already exists to make sure IDs are always generated
	* server-side; a non-existent ID is considered invalid even
	* for unsaved slides.
	*
	* @param string $id The ID to validate.
	*
	* @throws SlideNotFoundException if the ID doesn't already exist.
	*/
	public static function validate_id(string $id) {
		if (!self::exists($id)) {
			throw new SlideNotFoundException("Slide '$id' doesn't exist.");
		}
	}

	/**
	* Set the slide id. The ID must already exist to make
	* sure IDs are always generated server-side.
	*
	* @see Slide::validate_id() for validation exceptions.
	*
	* @param string $id The ID to set.
	*/
	public function set_id(string $id) {
		self::validate_id($id);
		$this->id = $id;
	}

	/**
	* Validate the slide markup.
	*
	* @param string $markup The slide markup.
	*
	* @throws ArgException if the markup is longer than SLIDE_MARKUP_MAX_LEN chars.
	*/
	public static function validate_markup(string $markup) {
		if (strlen($markup) > Config::limit('SLIDE_MARKUP_MAX_LEN')) {
			throw new ArgException("Slide markup too long.");
		}
	}

	/**
	* Set the slide markup.
	*
	* @see Slide::validate_markup() for validation exceptions.
	*
	* @param string $markup The slide markup.
	*/
	public function set_markup(string $markup) {
		self::validate_markup($markup);
		$this->markup = $markup;
	}

	/**
	* Validate the slide name.
	*
	* @param string $name The slide name.
	*
	* @throws ArgException if the slide name contains invalid characters.
	* @throws IntException if preg_match() fails.
	* @throws ArgException if the slide name is empty.
	* @throws ArgException if the slide name is longer than SLIDE_NAME_MAX_LEN chars.
	*/
	public static function validate_name(string $name) {
		$tmp = preg_match('/^[A-Za-z0-9_-]+$/', $name);
		if ($tmp === 0) {
			throw new ArgException("Invalid chars in slide name.");
		} else if ($tmp === NULL) {
			throw new IntException("preg_match() match failed.");
		}

		if (strlen($name) === 0) {
			throw new ArgException("Invalid empty slide name.");
		} else if (strlen($name) > Config::limit('SLIDE_NAME_MAX_LEN')) {
			throw new ArgException("Slide name too long.");
		}
	}

	/**
	* Set the slide name.
	*
	* @see Slide::validate_markup() for validation exceptions.
	*
	* @param string $name The slide name.
	*/
	public function set_name(string $name) {
		self::validate_name($name);
		$this->name = $name;
	}

	/**
	* Validate the slide index.
	*
	* @param int $index The slide index.
	*
	* @throws ArgException if $index < 0 or $index > SLIDE_MAX_INDEX.
	*/
	public static function validate_index(int $index) {
		if ($index < 0 || $index > Config::limit('SLIDE_MAX_INDEX')) {
			throw new ArgException("Slide index $index out of bounds.");
		}
	}

	/**
	* Set the slide index.
	*
	* @see Slide::validate_index() for validation exceptions.
	*
	* @param int $index The slide index.
	*/
	public function set_index(int $index) {
		self::validate_index($index);
		$this->index = $index;
	}

	/**
	* Validate the slide duration.
	*
	* @param int $duration The slide duration in seconds.
	*
	* @throws ArgException if $duration < SLIDE_MIN_DURATION or
	*                      $duration > SLIDE_MAX_DURATION.
	*/
	public static function validate_duration(int $duration) {
		if (
			$duration < Config::limit('SLIDE_MIN_DURATION')
			|| $duration > Config::limit('SLIDE_MAX_DURATION')
		) {
			throw new ArgException("Slide duration $duration out of bounds.");
		}
	}

	/**
	* Set the slide duration.
	*
	* @see Slide::validate_duration() for validation exceptions.
	*
	* @param int $duration The slide duration in seconds.
	*/
	public function set_duration(int $duration) {
		self::validate_duration($duration);
		$this->duration = $duration;
	}

	/**
	* Validate the slide owner.
	*
	* @param string $owner The slide owner.
	*
	* @throws ArgException if the user $owner doesn't exist.
	*/
	public static function validate_owner(string $owner) {
		if (!User::exists($owner)) {
			throw new ArgException("User $owner doesn't exist.");
		}
	}


	/**
	* Set the slide owner.
	*
	* @see Slide::validate_owner() for validation exceptions.
	*
	* @param string $owner The slide owner.
	*/
	public function set_owner(string $owner) {
		self::validate_owner($owner);
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
	* Validate the slide schedule start time.
	*
	* @param int $tstamp The starting timestamp in seconds.
	*
	* @throws ArgException if $tstamp < 0.
	*/
	public static function validate_sched_t_s(int $tstamp) {
		if ($tstamp < 0) {
			throw new ArgException(
				"Invalid negative schedule start timestamp."
			);
		}
	}

	/**
	* Set the slide schedule start time.
	*
	* @see Slide::validate_sched_t_s() for validation exceptions.
	*
	* @param int $tstamp The starting timestamp in seconds.
	*/
	public function set_sched_t_s(int $tstamp) {
		self::validate_sched_t_s($tstamp);
		$this->sched_t_s = $tstamp;
	}

	/**
	* Validate the slide schedule end time.
	*
	* @param int $tstamp The ending timestamp in seconds.
	*
	* @throws ArgException if $tstamp < 0.
	*/
	public static function validate_sched_t_e(int $tstamp) {
		if ($tstamp < 0) {
			throw new ArgException(
				"Invalid negative schedule end timestamp."
			);
		}
	}

	/**
	* Set the slide schedule end time.
	*
	* @see Slide::validate_sched_t_e() for validation exceptions.
	*
	* @param int $tstamp The ending timestamp in seconds.
	*/
	public function set_sched_t_e(int $tstamp) {
		self::validate_sched_t_e($tstamp);
		$this->sched_t_e = $tstamp;
	}

	/**
	* Validate the slide animation ID.
	*
	* @param int $anim The ID of the slide animation.
	*
	* @throws ArgException if $anim < 0.
	*/
	public static function validate_animation(int $anim) {
		if ($anim < 0) {
			throw new ArgException("Invalid negative animation.");
		}
	}

	/**
	* Set the slide animation ID.
	*
	* @see Slide::validate_animation() for validation exceptions.
	*
	* @param int $anim The ID of the slide animation.
	*/
	public function set_animation(int $anim) {
		self::validate_animation($anim);
		$this->animation = $anim;
	}

	/**
	* Validate the slide queue.
	*
	* @param string $name The queue name.
	*
	* @throws ArgException if a queue named $name doesn't exist.
	*/
	public static function validate_queue(string $name) {
		if (!Queue::exists($name)) {
			throw new ArgException("Queue '{$name}' doesn't exist.");
		}
	}

	/**
	* Set the slide queue.
	*
	* @see Slide::validate_queue() for validation exceptions.
	*
	* @param string $name The queue name.
	*/
	public function set_queue(string $name) {
		self::validate_queue($name);
		if ($this->queue_name != $name) {
			if ($this->queue_name) {
				// Remove slide from the old queue.
				$o = $this->get_queue();
				$o->remove_slide($this);
				$o->write();
			}

			// Add slide to the the new queue.
			$this->queue_name = $name;
			$n = new Queue();
			$n->load($name);
			$n->add($this);
			$n->write();
		}
	}

	/**
	* Validate the slide collaborators.
	*
	* @param array $collaborators An array of collaborator usernames.
	*
	* @throws ArgException if count($collaborators) > SLIDE_MAX_COLLAB.
	* @throws ArgException if a collaborator doesn't exist.
	*/
	public static function validate_collaborators(array $collaborators) {
		if (count($collaborators) > Config::limit('SLIDE_MAX_COLLAB')) {
			throw new ArgException("Too many collaborators.");
		}
		foreach ($collaborators as $k => $c) {
			if (!User::exists($c)) {
				throw new ArgException("User $c doesn't exist.");
			}
		}
	}

	/**
	* Set the slide collaborators.
	*
	* @see Slide::validate_collaborators() for validation exceptions.
	*
	* @throws ArgException if the slide owner is not set.
	* @throws ArgException if the slide owner is a collaborator.
	*
	* @param array $collaborators An array of collaborator usernames.
	*/
	function set_collaborators(array $collaborators) {
		self::validate_collaborators($collaborators);
		if (empty($this->get_owner())) {
			throw new ArgException("Can't set collaborators before owner.");
		}
		if (in_array($this->get_owner(), $collaborators)) {
			throw new ArgException("Can't set owner as collaborator.");
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
	* Check whether a slide is locked by a session.
	*
	* @param Session $session The session to check the lock against.
	*
	* @return bool TRUE if the slide is locked by $session and FALSE otherwise.
	*/
	public function is_locked_by(Session $session) {
		return (
			$this->lock !== NULL
			&& !$this->lock->is_expired()
			&& $this->lock->is_owned_by($session)
		);
	}

	/**
	* Store an uploaded asset in the 'assets' directory of this
	* slide. This function also generates a thumbnail for the asset.
	*
	* @param UploadedFile $file The upload data from $_FILE.
	*
	* @throws LimitException if the asset limit SLIDE_MAX_ASSETS is reached.
	*
	* @see SlideAsset::new() for additional exceptions.
	*/
	public function store_uploaded_asset(UploadedFile $file) {
		$this->assert_ready();

		if (
			!empty($this->assets)
			&& count($this->assets) >= Config::limit('SLIDE_MAX_ASSETS')
		) {
			throw new LimitException('Too many slide assets.');
		}

		if (!is_dir(self::get_asset_path($this->id))) {
			mkdir(self::get_asset_path($this->id));
		}

		$asset = new SlideAsset();
		$asset->new($file, $this);
		$this->assets[] = $asset;
	}

	/**
	* Remove an uploaded asset from a slide.
	*
	* @param string $name The name of the asset.
	*
	* @throws ArgException if the assetdoesn't exist.
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
	*
	* @throws ArgException if the asset doesn't exist.
	*
	* @return SlideAsset The requested asset.
	*/
	public function get_uploaded_asset(string $name): SlideAsset {
		foreach ($this->assets as $a) {
			if ($a->get_filename() === $name) { return $a; }
		}
		throw new ArgException("Asset '$name' doesn't exist.");
	}

	public function has_uploaded_asset(string $name): bool {
		try {
			$this->get_uploaded_asset($name);
		} catch (ArgException $e) {
			return FALSE;
		}
		return TRUE;
	}

	public function set_assets(array $assets) {
		$this->assets = $assets;
	}

	/**
	* Update $this->enabled based on whether a scheduled slide
	* should be enabled. This function overrides manual control
	* of $this->enabled.
	*/
	public function update_sched_enabled() {
		$t = time();
		if (
			$this->get_sched()
			&& $t >= $this->get_sched_t_s()
			&& $t <= $this->get_sched_t_e()
		) {
			$this->set_enabled(TRUE);
		} else if ($this->get_sched()){
			$this->set_enabled(FALSE);
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
			|| (
				$user->is_in_group('editor')
				&& (
					$this->get_owner() === $user->get_name()
					|| in_array(
						$user->get_name(),
						$this->get_collaborators(),
						TRUE
					)
				)
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
		$this->assert_ready();

		if (!is_dir(self::get_dir_path($this->id))) {
			mkdir(self::get_dir_path($this->id));
		}
		if (!is_dir(self::get_asset_path($this->id))) {
			mkdir(self::get_asset_path($this->id));
		}

		$tmp = JSONUtils::encode($this->export(TRUE, TRUE));
		Util::file_lock_and_put(self::get_conf_path($this->id), $tmp);
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
		Util::rmdir_recursive(self::get_dir_path($this->id));
	}

	public static function get_dir_path(string $id): string {
		assert(!empty($id), new \ArgException("Slide ID can't be empty."));

		return Config::config('LIBRESIGNAGE_ROOT')
			.Config::config('SLIDES_DIR')
			.'/'.$id;
	}

	public static function get_conf_path(string $id): string {
		return self::get_dir_path($id).'/conf.json';
	}

	public static function get_asset_path(string $id): string {
		return self::get_dir_path($id).'/assets';
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
		$queue = new Queue();
		$queue->load($this->queue_name);
		return $queue;
	}

	/**
	* List all existing Slide IDs.
	*
	* @return array An array of Slide IDs.
	*/
	public static function list_ids(): array {
		$ids = scandir(
			Config::config('LIBRESIGNAGE_ROOT')
			.Config::config('SLIDES_DIR')
		);

		// Remove '.', '..' and hidden files.
		return array_values(array_filter($ids, function(string $val) {
			return (
				substr($val, 0, 1) != '.'
				&& is_dir(self::get_dir_path($val))
				&& is_file(self::get_conf_path($val))
			);
		}));
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

	/**
	* Check whether a slide exists.
	*
	* @param string $id The ID of the slide to check.
	*
	* @return bool TRUE if $id exists, FALSE otherwise.
	*/
	public static function exists(string $id): bool {
		return in_array($id, self::list_ids(), TRUE);
	}
}

