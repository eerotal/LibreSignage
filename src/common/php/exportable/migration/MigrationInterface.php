<?php

namespace libresignage\common\php\exportable\migration;

/**
* An interface for defining migrations for Exportable data.
*
* Each class that implements MigrationInterface and is located in
* src/common/php/exportable/migration is used as a migration
* when migrating data between different versions of LibreSignage.
*/
interface MigrationInterface {
	/**
	* Return the classname this migration applies to.
	*
	* @return string The classname as a string.
	*/
	public static function from_class(): string;

	/**
	* Return the classname this migration converts to.
	*
	* @return string The classname as a string.
	*/
	public static function to_class(): string;
	
	/**
	* Return the original data version.
	*
	* @return string The version string.
	*/
	public static function from_version(): string;

	/**
	* Return the destination data version.
	*
	* @return string The version string.
	*/
	public static function to_version(): string;

	/**
	* Perform the migration of data.
	*
	* @param array The data to be migrated. This function modifies
	*              the array passed as the argument.
	*/
	public static function migrate(array &$data);
}
