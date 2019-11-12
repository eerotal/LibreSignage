<?php

namespace libresignage\common\php\exportable\migration;

use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\exportable\migration\MigrationIndex;
use libresignage\common\php\exportable\migration\exceptions\MigrationException;
use libresignage\common\php\Log;
use libresignage\common\php\Config;
use libresignage\common\php\Util;

/**
* A class for creating migrations from one data format
* version to another.
*/
final class MigrationPath {
	const INDEX_PATH = 'common/php/exportable/migration/index.json';
	const FALLBACK_ORIGIN_VERSION = '0.0.0';

	/**
	* Construct a new MigrationPath.
	*
	* @param &array $data       A reference to the data to migrate.
	* @param string $to_version The version to convert the data to.
	*
	* @throws MigrationException If no migration path from $from_version to
	*                            $to_version exists.
	*
	*/
	public function __construct(&$data, string $to_version) {
		assert(
			Util::array_is_subset(
				[
					Exportable::EXP_CLASSNAME,
					Exportable::EXP_VISIBILITY
				],
				array_keys($data)
			),
			'Metadata required when performing migration.'
		);

		$this->data = $data;
		$this->path = [];

		$this->index = new MigrationIndex();
		$this->index->load(
			Config::config('LIBRESIGNAGE_ROOT').'/'.self::INDEX_PATH
		);
		$this->build_path($to_version);
	}

	/**
	* Build the migration path for data.
	*
	* @param string $to_version The version to migrate the data to.
	*/
	public function build_path(string $to_version) {
		// Get origin version from $this->data.
		if (\array_key_exists(Exportable::EXP_VERSION, $this->data)) {
			$from_version = $this->data[Exportable::EXP_VERSION];
		} else {
			$from_version = self::FALLBACK_ORIGIN_VERSION;
		}

		$t = NULL;
		$ver = $from_version;
		while ($ver !== $to_version) {
			$t = $this->index->get(
				$this->data[Exportable::EXP_CLASSNAME],
				$ver
			);
			if ($t === NULL) {
				throw new MigrationException(
					"No migration path exists from ".
					"'$from_version' to '$to_version' for class ".
					"'{$this->data[Exportable::EXP_CLASSNAME]}'."
				);
			}
			$ver = $t->get_dest_version();
			array_push($this->path, $t);
		}
	}

	/**
	* Perform a migration.
	*
	* @return array|NULL The migrated data or NULL if no migration
	*                    took place.
	*/
	public function migrate() {
		if (count($this->path)) {
			foreach ($this->path as $t) {
				($t->get_migration_class())::migrate($this->data);

				// Update version field in data.
				$this->data[
					Exportable::EXP_VERSION
				] = ($t->get_migration_class())::to_version();
			}
			return $this->data;
		} else {
			return NULL;
		}
	}
}
