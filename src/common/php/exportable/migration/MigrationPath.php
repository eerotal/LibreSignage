<?php

namespace libresignage\common\php\exportable\migration;

use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\exportable\migration\MigrationIndex;
use libresignage\common\php\exportable\migration\exceptions\MigrationException;
use libresignage\common\php\exportable\ExportableDataContext;
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
	* @param &array                $data       The data to migrate.
	* @param string                $to_version The version to convert
	*                                          the data to.
	* @param ExportableDataContext $ctx        Exportable context data.
	*
	* @throws MigrationException If no migration path from $from_version to
	*                            $to_version exists.
	*
	*/
	public function __construct(
		$data,
		string $to_version,
		ExportableDataContext $ctx
	) {
		assert(
			Util::is_subset(
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
		$this->context = $ctx;

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
	*
	* @throws {MigrationException} If no classname is found in the data to be
	*                              migrated and all fallbacks fail.
	*/
	public function build_path(string $to_version) {
		/*
		* Get the origin version number from $this->data or use a fallback
		* if no version exists.
		*/
		if (array_key_exists(Exportable::EXP_VERSION, $this->data)) {
			$from_version = $this->data[Exportable::EXP_VERSION];
		} else {
			$from_version = self::FALLBACK_ORIGIN_VERSION;
			Log::logs(
				"No origin version defined for data to be migrated, ".
				"falling back to '$from_version'.", Log::LOGDEF
			);
		}

		/*
		* Get the origin classname from $this->data or use the current classname
		* from $this->context if no classname exists in $this->data. If neither
		* of these exists, throw an error.
		*
		* This check exists because early versions of LibreSignage had
		* inconsistent data formats but those still needed to be migrated.
		*/
		if (array_key_exists(Exportable::EXP_CLASSNAME, $this->data)) {
			$from_class = $this->data[Exportable::EXP_CLASSNAME];
		} else if ($this->context->has(ExportableDataContext::CLASSNAME)) {
			$from_class = $this->context->get(ExportableDataContext::CLASSNAME);
			Log::logs(
				"No origin class defined for data to be migrated, ".
				"falling back to '$from_class'.", Log::LOGDEF
			);
		} else {
			throw new MigrationException(
				"No classname found for data to be migrated ".
				"and all fallbacks failed."
			);
		}

		$t = NULL;
		$ver = $from_version;
		while ($ver !== $to_version) {
			$t = $this->index->get($from_class, $ver);
			if ($t === NULL) {
				throw new MigrationException(
					"No migration path exists from '$from_version' to ".
					"'$to_version' for class '$from_class'."
				);
			}
			$ver = $t->get_dest_version();
			array_push($this->path, $t);
		}
	}

	/**
	* Perform a migration.
	*
	* @return array|NULL The migrated data or NULL if no migration took place.
	*/
	public function migrate() {
		if (count($this->path)) {
			foreach ($this->path as $t) {
				($t->get_migration_class())::migrate(
					$this->data,
					$this->context
				);

				// Update version and classname fields in data.
				$this->data[
					Exportable::EXP_VERSION
				] = ($t->get_migration_class())::to_version();
				$this->data[
					Exportable::EXP_CLASSNAME
				] = ($t->get_migration_class())::to_class();
			}
			return $this->data;
		}
		return NULL;
	}
}
