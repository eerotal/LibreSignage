<?php

/*
*  Queue object definition for easily loading a list
*  list of all the slides in a specific slide queue.
*/

require_once(LIBRESIGNAGE_ROOT.'/common/php/util.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');

function queue_exists(string $name) {
	return in_array($name, queue_list());
}

function queue_list() {
	/*
	*  Get a list of the existing slide queue names.
	*/
	$queues = array();
	$queues = array_map(
		function(string $val) {
			if (substr($val, 0, 1) != '.' &&
				substr($val, -5) == '.json') {
				return substr($val, 0, strlen($val) - 5);
			} else {
				return NULL;
			}
		},
		scandir(LIBRESIGNAGE_ROOT.QUEUES_DIR)
	);
	$queues = array_values(array_filter($queues, function($val) {
		return $val != NULL;
	}));
	return $queues;
}

class Queue {
	const NAME_REGEX = '/^[A-Za-z0-9_-]$/';

	private $name   = NULL;
	private $owner  = NULL;
	private $slides = NULL;
	private $path   = NULL;
	private $loaded = FALSE;

	function __construct(string $name) {
		$this->set_name($name);
		$this->slides = [];
	}

	function load(bool $fix_errors = FALSE) {
		/*
		*  Load a queue from disk. If $fix_errors === TRUE,
		*  slides that can't be loaded are automatically
		*  removed from the queue.
		*/
		$errors_fixed = FALSE;

		if (!file_exists($this->path)) {
			throw new ArgException("Queue doesn't exist.");
		}
		$json = file_lock_and_get($this->path);
		$data = json_decode($json, $assoc=TRUE);
		if (json_last_error() != JSON_ERROR_NONE &&
			$data === NULL) {
			throw new IntException("JSON decoding failed: ".json_last_error_msg());
		}

		$this->set_owner($data['owner']);

		$this->slides = array();
		foreach ($data['slides'] as $n) {
			$tmp = new Slide();
			try {
				$tmp->load($n);
			} catch (Exception $e) {
				if (
					!(
						$e instanceof ArgException
						|| $e instanceof IntException
					)
					|| $fix_errors === FALSE
				) {
					throw $e;
				} else {
					$errors_fixed = TRUE;
					continue;
				}
			}
			$this->slides[] = $tmp;
		}

		// Write changes to disk in case any errors were fixed.
		if ($errors_fixed === TRUE) {
			$this->write();
		}

		$this->loaded = TRUE;
	}

	function write() {
		if (!$this->owner) {
			throw new ArgException(
				"Queue doesn't have an owner."
			);
		}
		$data = array(
				'owner' => $this->owner,
				'slides' => array_map(
					function($s) {
						return $s->get_id();
					},
					$this->slides
				)
			);
		$json = json_encode($data);
		if (json_last_error() != JSON_ERROR_NONE &&
			$json === FALSE) {
			throw new IntException(
				'JSON encoding failed: '.
				json_last_error_msg()
			);
		}
		file_lock_and_put($this->path, $json);
	}

	function normalize() {
		/*
		*  Normalize and sort the slide array of this queue.
		*/
		usort($this->slides, function(Slide $a, Slide $b) {
			if ($a->get_index() > $b->get_index()) {
				return 1;
			} else if ($a->get_index() < $b->get_index()) {
				return -1;
			} else {
				return 0;
			}
		});
		for ($i = 0; $i < count($this->slides); $i++) {
			$this->slides[$i]->set_index($i);
			$this->slides[$i]->write();
		}
	}

	function juggle(string $keep_id) {
		/*
		*  Recalculate slide indices so that the position of the
		*  slide with the id $keep_id stays the same, no unused
		*  indices remain and slides are sorted based on the
		*  indices.
		*/
		$keep = NULL;
		$clash = FALSE;

		// Remove the slide with ID $keep_id initially.
		foreach ($this->slides as $k => $s) {
			if ($s->get_id() == $keep_id) {
				$keep = $s;
				unset($this->slides[$k]);
				$this->slides = array_values(
					$this->slides
				);
				break;
			}
		}

		if (!$keep) {
			throw new ArgException(
				"Slide $keep_id doesn't exist in queue."
			);
		}

		$this->normalize();

		/*
		*  Shift indices so that the index of
		*  $keep_id is left free.
		*/
		$keep_i = $keep->get_index();
		foreach ($this->slides as $k => $s) {
			$s_i = $s->get_index();
			$clash |= $s_i == $keep_i;
			if ($s_i >= $keep_i) {
				$s->set_index($s_i + 1);
				$s->write();
			}
		}
		if (!$clash) {
			/*
			*  $keep_id didn't have the same
			*  index as any of the other slides
			*  -> make it the last one.
			*/
			$keep->set_index(count($this->slides));
			$keep->write();
		}

		// Add $keep back to $this->slides at the correct index.
		$this->slides[] = $keep;
		$this->normalize();
	}

	function remove() {
		if (!$this->loaded) {
			throw new IntException(
				'Queue not loaded.'
			);
		}
		if (!file_exists($this->path)) {
			throw new ArgException(
				"Queue doesn't exist."
			);
		}

		// Remove slides.
		foreach ($this->slides() as $s) {
			$s->remove();
		}

		// Remove queue.
		if (!unlink($this->path)) {
			throw new ArgException(
				"Failed to remove queue."
			);
		}
	}

	public static function set_name_dry(string $name) {
		/*
		*  Validate $name for use in Queue::set_name() without
		*  changing the name.
		*/
		if (strlen($name) === 0) {
			throw new ArgException('Invalid empty queue name.');
		} else if (strlen($name) > gtlim('QUEUE_NAME_MAX_LEN')) {
			throw new ArgException('Queue name too long.');
		}

		$tmp = preg_match(Queue::NAME_REGEX, $name);
		if ($tmp === 0) {
			throw new ArgException('Queue name contains invalid characters.');
		} else if ($tmp === FALSE) {
			throw new IntException('preg_match() failed.');
		}
	}

	function set_name(string $name) {
		Queue::set_name_dry($name);
		$this->name = $name;
		$this->path = LIBRESIGNAGE_ROOT.QUEUES_DIR.'/'.$name.'.json';
	}

	function set_owner(string $owner) {
		User::set_name_dry($owner);
		$this->owner = $owner;
	}

	function add(Slide $slide) {
		$this->slides[] = $slide;
	}

	function remove_slide(Slide $slide) {
		$this->slides = array_filter(
			$this->slides,
			function($s) use ($slide) {
				return $s->get_id() != $slide->get_id();
			}
		);
	}

	function slides() {
		return $this->slides;
	}

	function get_slide(string $id) {
		foreach($this->slides as $s) {
			if ($s->get_id() === $id) {
				return $s;
			}
		}
		return NULL;
	}

	function get_owner() {
		return $this->owner;
	}

	function get_data_array() {
		$sret = [];
		foreach ($this->slides as $s) {
			$sret[$s->get_id()] = $s->export(FALSE, FALSE);
		}
		return [
			'owner' => $this->owner,
			'slides' => $sret
		];
	}
}
