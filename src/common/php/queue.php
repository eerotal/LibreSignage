<?php

/*
*  Queue object definition for easily loading a list
*  list of all the slides in a specific slide queue.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

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
			if (substr($val, 1, 1) != '.' &&
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
	private $queue = NULL;
	private $owner = NULL;
	private $slides = NULL;
	private $path = NULL;
	private $loaded = FALSE;

	function __construct(string $queue) {
		if (!strlen($queue)) {
			throw new ArgException(
				'Invalid queue name.'
			);
		}

		$this->queue = $queue;
		$this->slides = array();
		$this->path = LIBRESIGNAGE_ROOT.QUEUES_DIR.
				'/'.$queue.'.json';
	}

	function load() {
		if (!file_exists($this->path)) {
			throw new ArgException(
				"Queue doesn't exist."
			);
		}
		$json = file_lock_and_get($this->path);
		$data = json_decode($json, $assoc=TRUE);
		if (json_last_error() != JSON_ERROR_NONE &&
			$data === NULL) {
			throw new IntException(
				"JSON decoding failed: ".
				json_last_error_msg()
			);
		}

		$this->set_owner($data['owner']);

		$this->slides = array();
		foreach ($data['slides'] as $n) {
			$tmp = new Slide();
			if (!$tmp->load($n)) {
				throw new IntException(
					"No such slide in queue."
				);
			}
			$this->slides[] = $tmp;
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

	function set_owner(string $owner) {
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

	function get_owner() {
		return $this->owner;
	}

	function get_data_array() {
		$sret = [];
		foreach ($this->slides as $s) {
			$sret[$s->get_id()] = $s->get_data_array();
		}
		return [
			'owner' => $this->owner,
			'slides' => $sret
		];
	}
}
