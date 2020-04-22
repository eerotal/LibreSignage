<?php

namespace libresignage\common\php\queue;

use libresignage\common\php\Config;
use libresignage\common\php\Util;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\auth\User;
use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\JSONUtils;
use libresignage\common\php\exceptions\JSONException;
use libresignage\common\php\exceptions\ArgException;
use libresignage\common\php\exceptions\IntException;
use libresignage\common\php\queue\exceptions\QueueNotFoundException;
use libresignage\common\php\queue\exceptions\BrokenQueueException;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;
use libresignage\common\php\exceptions\IllegalOperationException;
use libresignage\common\php\Log;

/**
* Queue class for handling LibreSignage queue data.
*/
final class Queue extends Exportable {
	const NAME_REGEX = '/^[A-Za-z0-9_-]+$/';

	const ENDPOS = -1;
	const NPOS = -2;

	private $name = '';
	private $owner = '';
	private $slides = [];
	private $slide_ids = [];

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
		return ['name', 'owner', 'slide_ids'];
	}

	public static function __exportable_public(): array {
		return ['name', 'owner', 'slide_ids'];
	}

	/*
	* Load a queue from file.
	*
	* @param string $name The name of the queue to load.
	*
	* @throws QueueNotFoundException If the requested queue doesn't exist.
	* @throws BrokenQueueException If errors occur during loading the Slides.
	*/
	public function load(string $name) {
		if (!self::exists($name)) {
			throw new QueueNotFoundException("Queue '{$name}' doesn't exist.");
		}

		$this->fimport(self::get_path($name));
		$this->load_slide_objects();
	}

	/**
	* Load the Slide objects of a Queue.
	*
	* This method clears the internal slides array first.
	*
	* @throws BrokenQueueException If loading a Slide fails.
	*/
	private function load_slide_objects() {
		$this->slides = [];

		foreach ($this->slide_ids as $n) {
			$s = new Slide();
			try {
				$s->load($n);
			} catch (\Exception $e) {
				throw $e;
				throw new BrokenQueueException(
					"Broken Slide '{$n}' in ".
					"Queue '{$this->get_name()}'."
				);
			}
			$this->slides[] = $s;
		}
	}

	/**
	* Write a Queue to disk.
	*
	* @throws IntException If the Queue is not ready.
	*/
	public function write() {
		$this->assert_ready();
		$json = JSONUtils::encode($this->export(TRUE, TRUE));
		Util::file_lock_and_put(self::get_path($this->name), $json);
	}

	/**
	* Remove a Queue.
	*
	* Slides that only exists in the Queue that's to be removed are
	* removed from the server completely.
	*
	* @throws IntException if the current queue is not ready.
	* @throws ArgException if the loaded queue doesn't exist.
	* @throws IntException if unlink() fails.
	*/
	function remove() {
		$this->assert_ready();

		if (!self::exists($this->name)) {
			throw new ArgException("Queue doesn't exist. Unsaved queue?");
		}

		foreach ($this->get_slides() as $s) {
			try {
				$s->remove_ref();
			} catch (IllegalOperationException $e) {
				// Slide would be removed from all Queues -> Remove Slide.
				$s->remove();
			}
		}

		if (!unlink(self::get_path($this->name))) {
			throw new IntException("Failed to remove queue.");
		}
	}

	/**
	* Validate $name to make sure it's a valid queue name.
	*
	* @param string $name The queue name to validate.
	* @throws ArgException if the queue name is not valid.
	* @throws IntException if preg_match() fails.
	*/
	public static function validate_name(string $name) {
		if (strlen($name) === 0) {
			throw new ArgException('Invalid empty queue name.');
		} else if (strlen($name) > Config::limit('QUEUE_NAME_MAX_LEN')) {
			throw new ArgException('Queue name too long.');
		}

		$tmp = preg_match(Queue::NAME_REGEX, $name);
		if ($tmp === 0) {
			throw new ArgException('Queue name contains invalid characters.');
		} else if ($tmp === FALSE) {
			throw new IntException('preg_match() failed.');
		}
	}

	public function set_name(string $name) {
		Queue::validate_name($name);
		$this->name = $name;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function set_owner(string $owner) {
		User::validate_name($owner);
		$this->owner = $owner;
	}

	public function get_owner(): string {
		return $this->owner;
	}

	/**
	* Add a Slide to a Queue at a specific position.
	*
	* @param Slide $slide The Slide object to add.
	* @param int   $at    The index where the Slide is added. If
	*                     Queue::ENDPOS is passed, the Slide is added
	*                     at the end of the Queue.
	*/
	public function add_slide(Slide $slide, int $at) {
		if ($at === self::ENDPOS) { $at = count($this->slide_ids); }

		array_splice($this->slide_ids, $at, 0, $slide->get_id());
		array_splice($this->slides, $at, 0, [$slide]);

		$slide->add_ref();
	}

	/**
	* Change the index of a Slide in a Queue.
	*
	* @param $slide Slide The Slide to move.
	* @param int    $to   The new index or Queue::ENDPOS for last.
	*
	* @throws SlideNotFoundexception If $slide doesn't exist in the Queue.
	*/
	public function reorder(Slide $slide, int $to) {
		if ($to === self::ENDPOS) { $to = count($this->slides) - 1; }

		$old = $this->get_index($slide);
		if ($old === self::NPOS) {
			throw new SlideNotFoundException(
				"Slide '{$slide->get_id()}' not found in ".
				"Queue '{$this->get_name()}'."
			);
		}

		array_splice($this->slide_ids, $old, 1);
		array_splice($this->slides, $old, 1);

		array_splice($this->slide_ids, $to, 0, $slide->get_id());
		array_splice($this->slides, $to, 0, [$slide]);
	}

	/**
	* Remove a Slide from a Queue.
	*
	* This function succeeds even when the supplied Slide doesn't
	* exist in the Queue.
	*
	* @param Slide $slide The Slide object to remove.
	*
	* @throws IllegalOperationException If the Slide wouldn't remain
	*                                   in any Queue.
	*/
	public function remove_slide(Slide $slide) {
		$slide->remove_ref();

		$this->slide_ids = array_values(array_filter(
			$this->slide_ids,
			function($id) use ($slide) {
				return $id !== $slide->get_id();
			}
		));
		$this->slides = array_values(array_filter(
			$this->slides,
			function($s) use ($slide) {
				return $s->get_id() !== $slide->get_id();
			}
		));
	}

	/**
	* Get an array with all the Slide objects of a Queue.
	*
	* @return array An array of Slide objects.
	*/
	public function get_slides(): array {
		return $this->slides;
	}

	/**
	* Get a Slide by ID.
	*
	* @param string $id  The ID of the Slide to get.
	*
	* @return Slide|NULL The Slide with ID $id or NULL if
	*                    no matching Slide exists.
	*/
	public function get_slide(string $id) {
		foreach($this->slides as $s) {
			if ($s->get_id() === $id) { return $s; }
		}
		return NULL;
	}

	/**
	* Get the index of a Slide in a Queue.
	*
	* @param Slide $slide The Slide to search for.
	*
	* @return int The index of the Slide or Queue::NPOS if not found.
	*/
	public function get_index(Slide $slide): int {
		for ($i = 0; $i < count($this->slide_ids); $i++) {
			if ($this->slide_ids[$i] === $slide->get_id()) {
				return $i;
			}
		}
		return self::NPOS;
	}

	/**
	* Get the last Slide in a Queue.
	*
	* If no Slides exist in the Queue, NULL is returned instead.
	*
	* @return Slide|NULL The last Slide in the Queue or NULL.
	*/
	public function get_last_slide() {
		if (!empty($this->slides)) {
			return end($this->slides);
		} else {
			return NULL;
		}
	}

	/**
	* Assert that a Queue object is ready. Use this as
	* a guard in functions that access files.
	*
	* @throws IntException if the queue is not ready.
	*/
	private function assert_ready() {
		if (
			empty($this->name)
			|| empty($this->owner)
		) {
			throw new IntException('Queue not ready.');
		}
	}

	/**
	* Get the path to the Queue data file.
	*
	* @param string $name The name of the queue.
	*
	* @return string The full path to the Queue data file.
	*/
	private static function get_path(string $name): string {
		return Config::config('LIBRESIGNAGE_ROOT')
				.Config::config('QUEUES_DIR')
				.'/'.$name.'.json';
	}

	/**
	* Check whether a Queue with name $name exists.
	*
	* @param string $name The Queue name.
	*
	* @return bool TRUE if the Queue exists and FALSE otherwise.
	*/
	public static function exists(string $name): bool {
		return in_array($name, self::list());
	}

	/**
	* Get an array with all the existing Queue names.
	*
	* @return array An array with all Queue names.
	*/
	public static function list(): array {
		$queues = array_map(
			function(string $val) {
				if (
					substr($val, 0, 1) != '.'
					&& substr($val, -5) == '.json'
				) {
					return substr($val, 0, strlen($val) - 5);
				} else {
					return NULL;
				}
			},
			scandir(
				Config::config('LIBRESIGNAGE_ROOT').
				Config::config('QUEUES_DIR')
			)
		);
		$queues = array_values(
			array_filter(
				$queues,
				function($val) {
					return $val != NULL;
				}
			)
		);
		return $queues;
	}
}
