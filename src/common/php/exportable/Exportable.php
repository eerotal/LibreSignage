<?php
/**
* An Exportable object implementation for easily exporting and importing
* object values in a format with only primitive values, ie. no objects.
* This is useful when object data needs to be JSON encoded/decoded for
* example.
*/

namespace libresignage\common\php\exportable;

use libresignage\common\php\Util;
use libresignage\common\php\JSONUtils;

abstract class Exportable {
	const EXP_CLASSNAME  = '__classname';
	const EXP_VISIBILITY = '__visibility';
	const EXP_RESERVED   = [self::EXP_CLASSNAME, self::EXP_VISIBILITY];

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
	* Getter functino which must be implemented in classes extending
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
	* Recursively export all object keys declared in static::$PUBLIC
	* or static::$PRIVATE depending on the value of $private. If
	* $meta === TRUE, Exportable metadata is included in the
	* returned data so that Exportable::import() can restore the
	* proper data structure when importing.
	*
	* @param bool $private If TRUE, also export properties listed in static::$PRIVATE.
	* @param bool $meta    If TRUE, metadata is also exported.
	*
	* @return array The exported data as an associative array.
	*
	* @throws ExportableException if a reserved key is used as an object property name.
	*/
	public function export(bool $private = FALSE, bool $meta = FALSE): array {
		$keys = [];
		$ret = [];

		if ($private) {
			$keys = static::$PRIVATE;
		} else {
			$keys = static::$PUBLIC;
		}

		if (!empty(array_intersect(self::EXP_RESERVED, $keys))) {
			throw new ExportableException(
				"Reserved key '".self::EXP_CLASSNAME."' used in object."
			);
		}

		if ($meta) { // Add metadata.
			$ret[self::EXP_CLASSNAME] = get_class($this);
			$ret[self::EXP_VISIBILITY] = $private ? 'private' : 'public';
		}

		foreach ($keys as $k) {
			$current = $this->__exportable_get($k);
			switch (gettype($current)) {
				case 'object':
					$ret[$k] = $this->exp_obj(
						$current, $private, $meta
					);
					break;
				case 'array':
					$ret[$k] = $this->exp_array(
						$current, $private, $meta
					);
					break;
				default:
					$ret[$k] = $current;
					break;
			}
		}
		return $ret;
	}

	/**
	* Handle object exporting.
	*
	* @param  mixed $obj     The object to export.
	* @param  bool  $private Parameter originally passed to Exportable::export();
	* @param  bool  $meta    Parameter originally passed to Exportable::export();
	*
	* @return array The exported object as an associative array.
	*
	* @throws ExportableException if $obj doesn't extend Exportable.
	*/
	private function exp_obj($obj, bool $private, bool $meta): array {
		if (is_subclass_of($obj, 'libresignage\\common\\php\\Exportable')) {
			return $obj->export($private, $meta);
		} else {
			throw new ExportableException(
				"Can't export a non-Exportable object."
			);
		}
	}

	/**
	* Handle array exporting.
	*
	* @param array $arr     The array to export.
	* @param bool  $private Parameter originally passed to Exportable::export();
	* @param bool  $meta    Parameter originally passed to Exportable::export();
	*
	* @return array The exported array.
	*/
	private function exp_array(array $arr, bool $private, bool $meta) {
		$ret = [];
		foreach ($arr as $k => $v) {
			switch(gettype($v)) {
				case 'object':
					$ret[$k] = $this->exp_obj($v, $private, $meta);
					break;
				case 'array':
					$ret[$k] = $this->exp_array($v, $private, $meta);
					break;
				default:
					$ret[$k] = $v;
					break;
			}
		}
		return $ret;
	}

	/**
	* Import Exportable data from file.
	*
	* @param string $path The path of the file to read.
	* @param bool   $lock If true, lock the file before reading.
	*/
	public function fimport(string $path, bool $lock = TRUE) {
		$tmp = Util::file_lock_and_get($path);
		$decoded = JSONUtils::decode($tmp, $assoc=TRUE);

		$t = new ExportableTransformation($data, $path);
		
		// Transform data and write it back to the file if needed.
		if ($t->transform()) {
			Util::file_lock_and_put(JSONUtils::encode($decoded));
		}

		$this->import($decoded, TRUE);
	}
	
	/**
	* Import object data from an array previously exported by
	* Exportable::export(). This function restores the proper
	* object types if metadata exporting was used when the data
	* was exported. If $check_keys is TRUE, the imported keys
	* are checked against the expected keys in either
	* static::$PUBLIC or static::$PRIVATE depending on which one
	* was used when exporting. Note that this also only works if
	* metadata was originally exported.
	*
	* @param array $data       The data to import.
	* @param bool  $check_keys If TRUE, check that the imported keys
	*                          match the original ones.
	*/
	public function import(array $data, bool $check_keys = FALSE) {
		foreach ($this->imp_array($data, TRUE, $check_keys) as $k => $v) {
			$this->__exportable_set($k, $v);
		}
	}

	/**
	* Handle array importing.
	*
	* @param array $arr        The array to import.
	* @param bool  $root       Whether this array is the root array or not.
	* @param bool  $check_keys Parameter originally passed to Exportable::import().
	*
	* @return array The imported data as an array.
	*
	* @throws ExportableException if the visibility value loaded from $arr is invalid.
	* @throws ExportableException if $check_keys === TRUE and the data keys don't match.
	*/
	private function imp_array(array $arr, bool $root, bool $check_keys) {
		if (
			$check_keys
			&& array_key_exists(self::EXP_VISIBILITY, $arr)
			&& array_key_exists(self::EXP_CLASSNAME, $arr)
		) {
			// Check that the keys in $arr match the expected ones.
			$keys = [];
			switch ($arr[self::EXP_VISIBILITY]) {
				case 'public':
					if ($root) {
						$keys = static::$PUBLIC;
					} else {
						$keys = $arr[self::EXP_CLASSNAME]::$PUBLIC;
					}
					break;
				case 'private':
					if ($root) {
						$keys = static::$PRIVATE;
					} else {
						$keys = $arr[self::EXP_CLASSNAME]::$PRIVATE;
					}
					break;
				default:
					throw new ExportableException(
						"Unknown visibility value."
					);
			}

			$diff = Util::arraydiff(
				$keys,
				array_diff(array_keys($arr), self::EXP_RESERVED)
			);
			if (!empty($diff['missing'])) {
				throw new ExportableException(
					"Missing keys from imported data: [ ".
					implode(', ', $diff['missing'])." ]"
				);
			}
			if (!empty($diff['extra'])) {
				throw new ExportableException(
					"Extra keys in imported data: [ ".
					implode(', ', $diff['extra'])." ]"
				);
			}
		}

		// Handle the actual array importing.
		$ret = NULL;
		if (!$root && array_key_exists(self::EXP_CLASSNAME, $arr)) {
			$ret = new $arr[self::EXP_CLASSNAME];
			$ret->import($arr);
		} else {
			$ret = [];
			foreach ($arr as $k => $v) {
				switch (gettype($v)) {
					case 'array':
						$ret[$k] = $this->imp_array(
							$v,
							FALSE,
							$check_keys
						);
						break;
					default:
						if (!in_array($k, self::EXP_RESERVED, TRUE)) {
							$ret[$k] = $v;
						}
						break;
				}
			}
		}
		return $ret;
	}
}
