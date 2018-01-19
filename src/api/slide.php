<?php

/*
*  Slide object implementation and utility definitions.
*  The Slide object is basically the interface between the raw
*  file data and the API endpoints.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/uid.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');

/*
*  These are the required data keys that a slide must contain in
*  order to be considered valid for use.
*/
define("SLIDE_REQ_KEYS", array(
	'id',
	'name',
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
		if ($a->get('index') > $b->get('index')) {
			return 1;
		} else if ($a->get('index') < $b->get('index')) {
			return -1;
		} else {
			return 0;
		}
	});
}

function juggle_slide_indices(string $force_index_for) {
	/*
	*  Recalculate the slide indices so that the indices
	*  for $force_index_for and the slides before it stay
	*  the same and the indices after $force_index_for are
	*  incremented by one. This function throws exceptions
	*  on errors.
	*/
	$forced_index = -1;

	$slides = get_slides_list();
	sort_slides_by_index($slides);

	foreach($slides as $s) {
		if ($s->get('id') == $force_index_for) {
			$forced_index = $s->get('index');
			break;
		}
	}
	if ($forced_index == -1) {
		throw new Exception("Slide ID not found in slides list!");
	}

	foreach ($slides as $s) {
		if ($s->get('id') != $force_index_for &&
			$s->get('index') >= $forced_index) {
			$s->set('index', $s->get('index') + 1);
		}
		$s->write();
	}
}

class Slide {
	public $REQ_KEYS = SLIDE_REQ_KEYS;
	private $paths = NULL;
	private $data = NULL;

	private function _mk_path_strs(string $id) {
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

	function clear() {
		$this->_clear_data();
		$this->_clear_paths();
	}

	function load(string $id) {
		/*
		*  Load the decoded data of a slide. This function
		*  throws errors on exceptions. No value is returned.
		*/
		$data_str = '';
		$this->_mk_path_strs($id);

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

	function get($key) {
		return $this->data[$key];
	}

	function get_data() {
		return $this->data;
	}

	function get_json_data() {
		return json_encode($this->data);
	}

	function set($key, $val) {
		/*
		*  Set the slide data key $key to $val.
		*  Returns the result of this->_verify()
		*  on the slide data afterwards.
		*/
		$this->data[$key] = $val;
		return $this->_verify($this->data);
	}

	function set_data(array $data) {
		/*
		*  Set the slide data. This function automatically
		*  verifies the data after it has been set and returns
		*  TRUE if the data is valid. If the data is invalid,
		*  FALSE is returned and no changes are made to the
		*  Slide object.
		*
		*  Extra: Normally the $data array must contain exactly
		*  the keys in SLIDE_REQ_KEYS in order to be considered
		*  valid, however this function makes an exception.
		*  If the 'id' key is not set in $data, this function
		*  automatically generates a new UID using the UID
		*  generator. This basicaly means creating a new slide.
		*  If the 'id' key is set, this function checks whether
		*  a slide with the ID actually exists and if it does,
		*  that slide is modified when saving etc. If the slide
		*  doesn't exist, however, this function returns FALSE.
		*/
		$tmp = $data;

		$this->_clear_data();
		$this->_clear_paths();

		if (empty($tmp['id'])) {
			/*
			*  If the ID isn't defined, generate it.
			*  This creates a new slide.
			*/
			try {
				$tmp['id'] = get_uid();
			} catch (Exception $e) {
				return FALSE;
			}
		} else if (!in_array($tmp['id'],
			get_slides_id_list(), TRUE)) {
			// Provided slide ID doesn't exist.
			return FALSE;
		}

		$this->_mk_path_strs($tmp['id']);
		$this->data = $tmp;
		return $this->_verify();
	}

	function write() {
		/*
		*  Write the currently stored data into the
		*  correct storage files. This function automatically
		*  overwrites files if they already exist. On failure
		*  exceptions are thrown.
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
