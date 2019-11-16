<?php

namespace libresignage\common\php\exportable\migration;

use libresignage\common\php\exportable\migration\exceptions\MigrationException;
use libresignage\common\php\util\VersionNumber;

/**
* A class representing an entry in a MigrationIndex.
*/
final class MigrationIndexEntry {
	/**
	* Construct a new MigrationIndexEntry.
	*
	* @param string $migration_class The classname of the migration class.
	* @param array  $from_version    The origin version string.
	* @param string $to_version      The result version string.
	* @param array  $from_class      The classname of the origin data class.
	* @param string $to_class        The classname of the destination class.
	*/
	public function __construct(
		string $migration_class,
		array $from_version,
		string $to_version,
		array $from_class,
		string $to_class
	) {
		$this->migration_class = $migration_class;
		$this->from_version = $from_version;
		$this->to_version = $to_version;
		$this->from_class = $from_class;
		$this->to_class = $to_class;
	}

	/**
	* Test whether a MigrationIndexEntry migrates data of a class from
	* a specific version to a newer one.
	*
	* @param string $class The classname of the data class.
	* @param string $from The origin version to test for.
	*
	* @return bool TRUE if the MigrationIndexEntry migrates data from
	*              the requested version, FALSE otherwise.
	*/
	public function migrates(string $class, string $version): bool {
		if (!in_array($class, $this->from_class)) { return FALSE; }

		$a = new VersionNumber([]);
		$b = new VersionNumber([]);

		$a->from_string($version);
		foreach ($this->from_version as $v) {
			$b->from_string($v);
			if ($b->matches($a)) { return TRUE; }
		}
		return FALSE;
	}

	/**
	* Get the destination version string.
	*
	* @return string A version string.
	*/
	public function get_dest_version(): string {
		return $this->to_version;
	}

	/**
	* Get the name of the migration class.
	*
	* @return string A classname.
	*/
	public function get_migration_class(): string {
		return $this->migration_class;
	}
}
