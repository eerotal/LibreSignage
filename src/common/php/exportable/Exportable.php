<?php

namespace libresignage\common\php\exportable;

use libresignage\common\php\exportable\exceptions\ExportableException;
use libresignage\common\php\exportable\migration\MigrationPath;
use libresignage\common\php\exportable\ExportableDataContext;
use libresignage\common\php\Util;
use libresignage\common\php\JSONUtils;
use libresignage\common\php\Log;
use libresignage\common\php\Config;
use libresignage\common\php\util\VersionNumber;

/**
* A class for serializing/deserializing objects.
*/
abstract class Exportable {
	const EXP_CLASSNAME  = '__classname';
	const EXP_VISIBILITY = '__visibility';
	const EXP_VERSION = '__version';

	const EXP_RESERVED = [
		self::EXP_CLASSNAME,
		self::EXP_VISIBILITY,
		self::EXP_VERSION
	];

	const EXP_VISIBILITY_PRIVATE = "private";
	const EXP_VISIBILITY_PUBLIC = "public";

	/**
	* Setter function which must be implemented in classes extending
	* Exportable as follows:
	*
	* public function __exportable_set(string $name, $value) {
	*     $this->{$name} = $value;
	* }
	*
	* @param string $name The name of the property to set.
	* @param mixed $value The value to set the property to.
	*/
	public abstract function __exportable_set(string $name, $value);

	/**
	* Getter function which must be implemented in classes extending
	* Exportable as follows:
	*
	* public function __exportable_get(string $name) {
	*     return $this->{$name};
	* }
	*
	* @param string $name The name of the property to get.
	*/
	public abstract function __exportable_get(string $name);

	/**
	* Write the object data to disk.
	*
	* Write the current data of the exportable to disk if that's an
	* operation normally supported by the object. This should probably
	* just be a wrapper function for the object's other methods or empty
	* if the object doesn't support writing to disk in the first place.
	*/
	public abstract function __exportable_write();

	/**
	* Return an array of private object properties.
	*
	* @return array An array of private object properties.
	*/
	public static abstract function __exportable_private(): array;

	/**
	* Return an array of public object properties.
	*
	* @return array An array of public object properties.
	*/
	public static abstract function __exportable_public(): array;

	/**
	* Recursively export object properties.
	*
	* @param bool $private Export private values.
	* @param bool $meta    Export metadata.
	*
	* @return array The exported data as an associative array.
	*
	* @throws ExportableException If a reserved key is used as
	*                             an object property name.
	*/
	public function export(bool $private = FALSE, bool $meta = FALSE): array {
		$keys = [];
		$ret = [];

		if ($private) {
			$keys = static::__exportable_private();
		} else {
			$keys = static::__exportable_public();
		}

		if (!empty(array_intersect(self::EXP_RESERVED, $keys))) {
			throw new ExportableException(
				"Reserved key '".self::EXP_CLASSNAME."' used in object."
			);
		}

		/*
		* Convert the Exportable object into an associative array with all
		* the required values and export it using Exportable::export_array().
		*/
		$data = [];
		foreach ($keys as $k) { $data[$k] = $this->__exportable_get($k); }
		$ret = self::export_array($data, $private, $meta);

		// Add metadata.
		if ($meta) {
			$ret[self::EXP_CLASSNAME] = get_class($this);
			$ret[self::EXP_VISIBILITY] = $private ? 'private' : 'public';
			$ret[self::EXP_VERSION] = self::current_version();
		}

		return $ret;
	}

	/**
	* Export an array.
	*
	* @param array $arr     The array to export.
	* @param bool  $private Export private values.
	* @param bool  $meta    Export metadata.
	*
	* @return array The exported array.
	*/
	private static function export_array(
		array $data,
		bool $private,
		bool $meta
	) {
		$ret = [];
		foreach ($data as $k => $v) {
			switch (gettype($v)) {
				case 'object':
					# Object
					if (is_subclass_of($v, self::class)) {
						$ret[$k] = $v->export($private, $meta);
					} else {
						throw new ExportableException(
							"Can't export a non-Exportable object."
						);
					}
					break;
				case 'array':
					# Array
					$ret[$k] = self::export_array($v, $private, $meta);
					break;
				default:
					# Primitive value
					$ret[$k] = $v;
					break;
			}
		}
		return $ret;
	}

	/**
	* Import Exportable object data from file.
	*
	* If any data migration is required, the migrated data is automatically
	* written back to the original file.
*
	* @param string $path The path of the file to read.
	*/
	public function fimport(string $path) {
		$migrated = FALSE;

		$tmp = Util::file_lock_and_get($path);
		$decoded = JSONUtils::decode($tmp, $assoc=TRUE);

		$ctx = new ExportableDataContext();
		$ctx->set(ExportableDataContext::FILEPATH, $path);
		$ctx->set(ExportableDataContext::CLASSNAME, get_class($this));

		$ret = $this->reconstruct_object($decoded, $ctx, FALSE, $migrated);

		// Write migrated data back to file.
		if ($migrated) {
			Log::logs(
				"Migrated data of '".get_class($ret).
				"' from file '$path'.", Log::LOGDEF
			);
			$this->__exportable_write();
		}
	}

	/**
	* Reconstruct Exportable object data.
	*
	* If data migration is required, this function automatically
	* migrates the data first.
	*
	* This function automatically reconstructs objects from metadata
	* included in the data that's being imported. Note that this function
	* will fail if you supply a $data array that doesn't contain the
	* required metadata fields.
	*
	* @param array                 $data      The data to reconstruct.
	* @param ExportableDataContext $ctx       Exportable context data.
	* @param bool                  $return    If TRUE, a new object is created
	*                                         for the data and the new object is
	*                                         returned. Otherwise the data is
	*                                         set into $this and $this is
	*                                         returned.
	* @param bool                  &$migrated A reference to a boolean that's
	*                                         set to TRUE if migration took
	*                                         place. Otherwise this is set to
	*                                         FALSE.
	*
	* @throws AssertionError If $data doesn't contain metadata.
	*/
	public function reconstruct_object(
		array $data,
		ExportableDataContext $ctx,
		bool $return = TRUE,
		bool &$migrated = FALSE
	) {
		$tmp = self::migrate($data, $ctx);
		if ($tmp == NULL) {
			$tmp = $data;
			$migrated = FALSE;
		} else {
			$migrated = TRUE;
		}

		self::check_data_keys($tmp);

		$tmp = self::reconstruct_array($tmp);
		$obj = ($return) ? new $tmp[self::EXP_CLASSNAME]() : $this;
		foreach ($tmp as $k => $v) { $obj->__exportable_set($k, $v); }

		return $obj;
	}

	/**
	* Recursively reconstruct an array that contains Exportable data.
	*
	* The "root" element is left as an array and is returned with all
	* nested Exportable objects reconstructed.
	*
	* @param array $data       The array to reconstruct.
	*
	* @return array The reconstructed data.
	*/
	private function reconstruct_array(array $data): array {
		$ret = [];
		foreach ($data as $k => $v) {
			if (is_array($v)) {
				if (self::has_metadata($v)) {
					// Nested object.
					$ret[$k] = $this->reconstruct_object(
						$v,
						new ExportableDataContext(),
						TRUE
					);
				} else {
					// Bare array.
					$ret[$k] = $this->reconstruct_array($v);
				}
			} else {
				// Primitive value.
				$ret[$k] = $v;
			}
		}
		return $ret;
	}

	/**
	* Migrate an array of Exportable data.
	*
	* @param array                 $data The data to migrate.
	* @param ExportableDataContext $ctx  Exportable context data.
	*
	* @return array|NULL The migrated data or NULL if no migration took place.
	*
	* @throws AssertionError If $data doesn't contain metadata.
	*/
	public static function migrate(
		array $data,
		ExportableDataContext $ctx
	) {
		assert(self::has_metadata($data));
		$p = new MigrationPath($data, self::current_version(), $ctx);
		return $p->migrate();
	}

	/**
	* Get a cleaned-up LibreSignage version string.
	*
	* @return string A version string.
	*/
	private static function current_version(): string {
		$ver = new VersionNumber([]);
		$ver->from_string(Config::config('LS_VER'));
		return strval($ver);
	}

	/**
	* Check whether an array contains Exportable metadata.
	*
	* This function doesn't require the Exportable::EXP_VERSION key
	* to exist in $data.
	*
	* @return bool TRUE if metadata exists, FALSE otherwise.
	*/
	public static function has_metadata(array $data): bool {
		return Util::is_subset(
			[
				Exportable::EXP_CLASSNAME,
				Exportable::EXP_VISIBILITY
			],
			array_keys($data)
		);
	}

	/**
	* Check that a data array contains the required keys.
	*
	* @param array $data The data to check.
	*
	* @throws ExportableException if missing or extra keys are found.
	*/
	public static function check_data_keys(array $data) {
		$keys = [];
		switch ($data[self::EXP_VISIBILITY]) {
			case self::EXP_VISIBILITY_PUBLIC:
				$keys = $data[self::EXP_CLASSNAME]::__exportable_public();
				break;
			case self::EXP_VISIBILITY_PRIVATE:
				$keys = $data[self::EXP_CLASSNAME]::__exportable_private();
				break;
			default:
				throw new ExportableException("Unknown visibility value.");
		}

		$diff = Util::arraydiff(
			$keys,
			array_diff(array_keys($data), self::EXP_RESERVED)
		);
		if (!empty($diff['missing'])) {
			throw new ExportableException(
				"Missing keys from imported data: [ ".
				implode(', ', $diff['missing'])." ] for class ".
				$data[self::EXP_CLASSNAME]
			);
		}
		if (!empty($diff['extra'])) {
			throw new ExportableException(
				"Extra keys in imported data: [ ".
				implode(', ', $diff['extra'])." ] for class ".
				$data[self::EXP_CLASSNAME]
			);
		}
	}
}
