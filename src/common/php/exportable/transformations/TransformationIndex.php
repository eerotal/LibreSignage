<?php

namespace libresignage\common\php\exportable\transformations;

use libresignage\common\php\exportable\exceptions\ExportableTransformationException;
use libresignage\common\php\JSONUtils;
use libresignage\common\php\Util;

/**
* A class representing a transformation index.
*/
final class TransformationIndex {
	/**
	* Construct a new TransformationIndex.
	*/
	public function __construct() {
		$this->index = NULL;
	}

	/**
	* Load the transformation index.
	*
	* @param string $path The filepath of the index file.
	*
	* @return array The transformation index as a associative array.
	*
	* @throws ExportableTransformationException If the index file doesn't exist.
	*/
	public function load(string $file) {
		if (!is_file($file)) {
			throw new ExportableTransformationException(
				"Transformation index missing!"
			);
		}

		$tmp = Util::file_lock_and_get($file);
		foreach (JSONUtils::decode($tmp, $assoc=TRUE) as $from => $data) {
			$index[$from] = new TransformationIndexEntry(
				$from,
				$data['to'],
				$data['fqcn']
			);
		}
		self::sort_index($index);

		$this->index = $index;
	}

	/**
	* Get a transformation index entry for a data version.
	*
	* @param string $from The origin version.
	*
	* @return TransformationIndexEntry|NULL The corresponding entry or NULL
	*                                       if not found.
	*/
	public function get(string $from) {
		foreach ($this->index as $key => $t) {
			if ($t->transforms($from)) {
				return $t;
			}
		}
		return NULL;
	}
	
	/**
	* Sort a transformation index by the keys, ie. version numbers.
	*
	* @return bool TRUE on success or FALSE on failure. 
	*/
	public static function sort_index(array &$index) {
		return uksort($index, function ($a, $b) {
			$a_split = explode('.', $a);
			$b_split = explode('.', $b);
			assert(count($a_split) == count($b_split));
			
			for ($i = 0; $i < count($a_split); $i++) {
				if ($a_split[$i] < $b_split[$i]) {
					return -1;
				} else if ($a_split[$i] > $b_split[$i]) {
					return 1;
				}
			}
			return 0;
		});
	}
}
