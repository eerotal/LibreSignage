<?php

/*
*  Slide object implementation and utility definitions.
*  The Slide object is basically the interface between the raw
*  file data and the API endpoints.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/uid.php');
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
	const REQ_KEYS = array(
		'id',
		'name',
		'index',
		'time',
		'markup',
		'owner'
	);

	const K_CONF = 'config';
	const K_MARKUP = 'markup';
	const K_ID = 'id';

	const K_DIR = 'dir';

	private $files = NULL;
	private $dirs = NULL;
	private $data = NULL;

	private function _mk_paths(string $id) {
		$this->dirs = array(
			self::K_DIR => LIBRESIGNAGE_ROOT.SLIDES_DIR.'/'.$id
		);
		$this->files = array(
			self::K_CONF => $this->dirs[self::K_DIR].
						'/conf.json',
			self::K_MARKUP => $this->dirs[self::K_DIR].
						'/markup.dat'
		);
	}

	private function _paths_exist() {
		// Check that all required files and dirs exist.
		foreach ($this->files as $f) {
			if (!is_file($f)) {
				return FALSE;
			}
		}

		foreach ($this->dirs as $d) {
			if (!is_dir($d)) {
				return FALSE;
			}
		}
		return TRUE;
	}

	private function _verify() {
		/*
		*  Verify the currently stored data.
		*/
		if (array_is_equal(array_keys($this->data),
					self::REQ_KEYS)) {
			return TRUE;
		}
		return FALSE;
	}

	function load(string $id) {
		/*
		*  Load the decoded data of a slide. This function
		*  throws errors on exceptions. No value is returned.
		*/
		$dstr = '';
		$this->data = NULL;

		$this->_mk_paths($id);
		if (!$this->_paths_exist()) {
			return FALSE;
		}

		// Read data.
		$dstr = file_lock_and_get($this->files[self::K_CONF]);
		if ($dstr === FALSE) {
			throw new IntException("Slide config read error!");
		}

		$this->data = json_decode($dstr, $assoc=TRUE);
		if ($this->data == NULL &&
			json_last_error() != JSON_ERROR_NONE) {

			throw new IntException("Slide config decode error!");
		}

		// Read markup.
		$this->data[self::K_MARKUP] = file_lock_and_get(
			$this->files[self::K_MARKUP]
		);
		if ($this->data[self::K_MARKUP] === FALSE) {
			throw new IntException("Slide markup read error!");
		}

		$this->data[self::K_ID] = $id;

		if (!$this->_verify()) {
			throw new IntException("Slide data is invalid!");
		}
		return TRUE;
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
		*  the keys in REQ_KEYS in order for it to be considered
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
		$this->data = NULL;

		if (empty($tmp[self::K_ID])) {
			/*
			*  If the ID isn't defined, generate it.
			*  This creates a new slide.
			*/
			$tmp[self::K_ID] = get_uid();
		} else if (!in_array($tmp[self::K_ID],
			get_slides_id_list(), TRUE)) {
			// Provided slide ID doesn't exist.
			return FALSE;
		}

		$this->_mk_paths($tmp[self::K_ID]);
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
		$tmp = $this->data;
		unset($tmp[self::K_MARKUP]);
		$cstr = json_encode($tmp);
		if ($cstr === FALSE &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new IntException("Slide config ".
						"encode failed!");
		}
		file_lock_and_put($this->files[self::K_CONF], $cstr);
		file_lock_and_put($this->files[self::K_MARKUP],
				$this->data[self::K_MARKUP]);
	}

	function remove() {
		if (!empty($this->dirs[self::K_DIR])) {
			rmdir_recursive($this->dirs[self::K_DIR]);
		}
	}
}

