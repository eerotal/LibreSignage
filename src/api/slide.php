<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');

define("SLIDE_REQ_KEYS", array(
	'id',
	'index',
	'time',
	'markup'
));

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

class Slide {
	public $REQ_KEYS = SLIDE_REQ_KEYS;
	private $paths = NULL;
	private $data = NULL;

	private function _mk_paths(string $id) {
		$this->paths['dir'] = LIBRESIGNAGE_ROOT.
					SLIDES_DIR.'/'.$id;
		$this->paths['config'] = $this->paths['dir'].
					'/conf.json';
		$this->paths['markup'] = $this->paths['dir'].
					'/markup.html';
	}

	private function _clear_paths() {
		$this->paths = NULL;
	}

	private function _clear_data() {
		$this->data = NULL;
	}

	private function _verify() {
		/*
		*  Verify the currently stored data.
		*/
		if (array_is_equal(array_keys($this->data),
					$this->REQ_KEYS)) {
			return TRUE;
		}
		return FALSE;
	}

	function load(string $id) {
		/*
		*  Load the decoded data of a slide. This function
		*  throws errors on exceptions. No value is returned.
		*/
		$data_str = '';

		$this->_mk_paths($id);

		// Check that all required files exist.
		if (!is_dir($this->paths['dir'])) {
			throw new Exception("Slide directory doesn't".
						"exist!");
			$this->_clear_paths();
		}
		if (!is_file($this->paths['config'])) {
			throw new Exception("Slide config doesn't ".
						"exist!");
			$this->_clear_paths();
		}
		if (!is_file($this->paths['markup'])) {
			throw new Exception("Slide markup doesn't ".
						"exist!");
			$this->_clear_paths();
		}

		// Read data.
		$data_str = @file_get_contents($this->paths['config']);
		if ($data_str === FALSE) {
			throw new Exception("Slide config read error!");
		}
		$this->data = json_decode($data_str, $assoc=TRUE);
		if ($this->data == NULL) {
			throw new Exception("Slide config decode error!");
		}
		$this->data['markup'] = @file_get_contents(
					$this->paths['markup']);
		if ($this->data['markup'] == FALSE) {
			throw new Exception("Slide markup read error!");
			$this->_clear_data();
		}
		$this->data['id'] = $id;
		if (!$this->_verify()) {
			throw new Exception("Slide data is invalid!");
			$this->_clear_data();
		}
	}

	function get_data() {
		return $this->data;
	}

	function get_json_data() {
		return json_encode($this->data);
	}

	function set(string $id, array $data) {
		/*
		*  Set the slide data. This function automatically
		*  verifies the data after it has been set and returns
		*  TRUE if the data is valid. FALSE is returned
		*  otherwise.
		*/
		$this->_mk_paths($id);
		$this->data = $data;
		return $this->_verify();
	}

	function write() {
		/*
		*  Write the currently stored data into the
		*  correct storage files. This function throws
		*  exceptions on errors. No value is returned.
		*/
		if (!file_exists($this->paths['dir']) ||
			!is_dir($this->paths['dir'])) {
			if (!@mkdir($this->paths['dir'], 0775, true)) {
				throw new Exception("Failed to create ".
						"slide directory!");
			}
		}

		$tmp = $this->data;
		unset($tmp['markup']);
		$conf_str = json_encode($tmp);

		if ($conf_str === FALSE) {
			throw new Exception("Slide config encode failed!");
		}
		if (!file_put_contents($this->paths['config'],
					$conf_str)) {
			throw new Exception("Failed to write slide ".
						"config!");
		}
		if (!file_put_contents($this->paths['markup'],
					$this->data['markup'])) {
			throw new Exception("Failed to write slide ".
						"markup!");
		}
	}
}
