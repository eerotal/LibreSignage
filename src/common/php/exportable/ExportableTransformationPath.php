<?php

namespace libresignage\common\php\exportable;

use libresignage\common\php\exportable\transformations\TransformationIndex;
use libresignage\common\php\exportable\exceptions\ExportableTransformationException;
use libresignage\common\php\Log;
use libresignage\common\php\Config;

/**
* A class for creating transformations from one data format
* version to another.
*/
final class ExportableTransformationPath {
	const INDEX_PATH = 'common/php/exportable/transformations/index.json';
	const FALLBACK_ORIGIN_VERSION = '0.0.0';

	/**
	* Construct a new ExportableTransformationPath.
	*
	* @param &array $data A reference to the data to transform.
	* @param string $path The original filepath of the data. This
	*                     is only used for logging.
	* @param string $to   The version to convert the data to.
	*
	* @throws ExportableTransformationException If no transformation path
	*                                           from $from to $to exists.
	*
	*/
	public function __construct(&$data, string $filepath, string $to) {
		assert(
			array_key_exists(self::EXP_CLASSNAME),
			"Metadata required when performing transformations."
		);
		assert(
			array_key_exists(self::EXP_VISIBILITY),
			"Metadata required when performing transformations."
		);

		$this->data = $data;
		$this->filepath = $filepath;
		$this->path = [];

		$this->index = new TransformationIndex();
		$this->index->load(
			Config::config('LIBRESIGNAGE_ROOT').'/'.self::INDEX_PATH
		);
		$this->build_path($to);
	}

	/**
	* Build the transformation path for data.
	*
	* @param string $to The version to transform the data to.
	*/
	public function build_path(string $to) {
		// Get origin version from $this->data.
		if (\array_key_exists('version', $this->data)) {
			$from = $this->data['version'];
		} else {
			$from = self::FALLBACK_ORIGIN_VERSION;
		}

		$t = NULL;
		$ver = $from;
		while ($ver !== $to) {
			$t = $this->index->get($ver);
			if ($t === NULL) {
				throw new ExportableTransformationException(
					"No transformation path exists from '$from' to '$to'."
				);
			}
			$ver = $t->get_result_version();
			array_push($this->path, $t);
		}
	}

	/**
	* Perform a transformation.
	*
	* @return array The transformed data.
	*/
	public function transform() {
		if (count($this->path)) {
			Log::logs("Transforming data from '$this->filepath'.", Log::LOGDEF);
			foreach ($this->path as $t) {
				($t->get_fqcn())::transform($this->data);
			}
			return $this->data;
		}
		Log::logs("Data transformed for '$this->filepath'.", Log::LOGDEF);
	}
}
