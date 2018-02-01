<?php

/*
*  Slide object implementation and utility definitions.
*  The Slide object is basically the interface between the raw
*  file data and the API endpoints.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/uid.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

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
		$s->set('index', $i);
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
		if ($slides[$i]->get('id') == $force_index_for) {
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
			$s_i = $s->get('index');
			$f_i = $forced->get('index');
			if ($s_i >= $f_i) {
				// Advance indices after $forced by one.
				$s->set('index', $s_i + 1);
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
		$forced->set('index', $unused);
		$forced->write();
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
		try {
			$data_str = file_lock_and_get(
				$this->paths['config']);
		} catch(Exception $e) {
			throw $e;
		}

		if ($data_str === FALSE) {
			throw new Exception("Slide config read error!");
		}
		$this->data = json_decode($data_str, $assoc=TRUE);
		if ($this->data == NULL) {
			throw new Exception("Slide config decode error!");
		}

		try {
			$this->data['markup'] = file_lock_and_get(
						$this->paths['markup']);
		} catch(Exception $e) {
			throw $e;
		}

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
		try {
			file_lock_and_put($this->paths['config'],
					$conf_str);
		} catch(Exception $e) {
			throw $e;
		}

		try {
			file_lock_and_put($this->paths['markup'],
					$this->data['markup']);
		} catch(Exception $e) {
			throw $e;
		}
	}
}
