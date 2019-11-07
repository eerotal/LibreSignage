<?php

namespace libresignage\common\php\exportable;

use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\exportable\transformations\TransformationIndex;
use libresignage\common\php\exportable\exceptions\ExportableTransformationException;
use libresignage\common\php\Log;
use libresignage\common\php\Config;
use libresignage\common\php\Util;

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
	* @param string $to   The version to convert the data to.
	*
	* @throws ExportableTransformationException If no transformation path
	*                                           from $from to $to exists.
	*
	*/
	public function __construct(&$data, string $to) {
		assert(
			Util::array_is_subset(
				[
					Exportable::EXP_CLASSNAME,
					Exportable::EXP_VISIBILITY
				],
				array_keys($data)
			),
			'Metadata required when performing transformations.'
		);

		$this->data = $data;
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
		if (\array_key_exists(Exportable::EXP_VERSION, $this->data)) {
			$from = $this->data[Exportable::EXP_VERSION];
		} else {
			$from = self::FALLBACK_ORIGIN_VERSION;
		}

		$t = NULL;
		$ver = $from;
		while ($ver !== $to) {
			$t = $this->index->get(
				$this->data[Exportable::EXP_CLASSNAME],
				$ver
			);
			if ($t === NULL) {
				throw new ExportableTransformationException(
					"No transformation path exists from '$from' to '$to' ".
					"for class '{$this->data[Exportable::EXP_CLASSNAME]}'."
				);
			}
			$ver = $t->get_result_version();
			array_push($this->path, $t);
		}
	}

	/**
	* Perform a transformation.
	*
	* @return array|NULL The transformed data or NULL if no transformation
	*                    took place.
	*/
	public function transform() {
		if (count($this->path)) {
			foreach ($this->path as $t) {
				($t->get_fqcn())::transform($this->data);

				// Update version field in data.
				$this->data[
					Exportable::EXP_VERSION
				] = ($t->get_fqcn())::to_version();
			}
			return $this->data;
		} else {
			return NULL;
		}
	}
}
