<?php

namespace libresignage\common\php\exportable;

use libresignage\common\php\exportable\transformations\TransformationIndex;
use libresignage\common\php\Log;

/**
* A class for creating transformations from one data format
* version to another.
*/
final class ExportableTransformation {
	/**
	* Construct a new ExportableTransformation.
	*
	* @param &array $data A reference to the data to transform.
	* @param string $path The original filepath of the data. This
	*                     is only used for logging.
	*
	*/
	public function __construct(&$data, string $path) {
		$this->data = $data;
		$this->path = $path;
	}

	public static function build_transform_path(
		string $from,
		string $to
	): array {
		$index = TransformationIndex::load();

		foreach ($index as $key => $t) {
			
		}
	}
	
	/**
	* Perform a transformation.
	*
	* @param string $current_version The current version string.
	*
	* @return bool TRUE if a transformation was performed, FALSE otherwise.
	*/
	public static function transform(string $current_version) {
		assert(
			array_key_exists(self::EXP_CLASSNAME),
			"Metadata required when performing transformations."
		);
		assert(
			array_key_exists(self::EXP_VISIBILITY),
			"Metadata required when performing transformations."
		);

		$version = NULL;
		if (array_key_exists('version', $data)) {
			$version = $data['version'];
		}

		if ($current_version !== $version) {
			Log::logs("Transforming data for ".$original_path, Log::LOGDEF);
			return TRUE;
		}

		return FALSE;
	}
}
