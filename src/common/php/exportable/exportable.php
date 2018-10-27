<?php

/*
*  An Exportable object implementation for easily exporting and importing
*  object values in a format with only primitive values, ie. no objects.
*  This is useful when object data needs to be JSON encoded/decoded for
*  example.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

class ExportableException extends Exception {}

const EXP_CLASSNAME = '__classname';
const EXP_VISIBILITY   = '__visibility';

const EXP_RESERVED  = [
	EXP_CLASSNAME,
	EXP_VISIBILITY
];

abstract class Exportable {
	/*
	*  Getter and setter functions which need to be implemented in
	*  the objects extending this class. These are used by various
	*  functions to access the variables within the Exportable
	*  objects. These functions should be implemented as follows.
	*
	*  public function __exportable_set(string $name, $value) {
	*      $this->{$name} = $value;
	*  }
	*
	*  public function __exportable_get(string $name) {
	*      return $this->{$name};
	*  }
	*/
	public abstract function __exportable_set(string $name, $value);
	public abstract function __exportable_get(string $name);

	public function export(bool $private = FALSE, bool $meta = FALSE) {
		/*
		*  Recursively export all object keys declared in static::$PUBLIC
		*  or static::$PRIVATE depending on the value of $private. If
		*  $meta === TRUE, Exportable metadata is included in the
		*  returned data so that Exportable::import() can restore the
		*  proper data structure when importing.
		*/
		$keys = [];
		$ret = [];

		if ($private) {
			$keys = static::$PRIVATE;
		} else {
			$keys = static::$PUBLIC;
		}

		if (!empty(array_intersect(EXP_RESERVED, $keys))) {
			throw new ExportableException(
				"Reserved key '".EXP_CLASSNAME."' used in object."
			);
		}

		if ($meta) { // Add metadata.
			$ret[EXP_CLASSNAME] = get_class($this);
			$ret[EXP_VISIBILITY] = $private ? 'private' : 'public';
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

	private function exp_obj($obj, bool $private, bool $meta) {
		// Handle object exporting.
		if (is_subclass_of($obj, 'Exportable')) {
			return $obj->export($private, $meta);
		} else {
			throw new ExportableException(
				"Can't export a non-Exportable object."
			);
		}
	}

	private function exp_array(array $arr, bool $private, bool $meta) {
		// Handle array exporting.
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

	public function import(array $data, bool $check_keys = FALSE) {
		/*
		*  Import object data from an array previously exported by
		*  Exportable::export(). This function restores the proper
		*  object types if metadata exporting was used when the data
		*  was exported. If $check_keys is TRUE, the imported keys
		*  are checked against the expected keys in either
		*  static::$PUBLIC or static::$PRIVATE depending on which one
		*  was used when exporting. Note that this also only works if
		*  metadata was originally exported.
		*/
		foreach ($this->imp_array($data, TRUE, $check_keys) as $k => $v) {
			$this->__exportable_set($k, $v);
		}
	}

	private function imp_array(
		array $arr,
		bool $root,
		bool $check_keys
	) {
		if (
			$check_keys
			&& array_key_exists(EXP_VISIBILITY, $arr)
			&& array_key_exists(EXP_CLASSNAME, $arr)
		) {
			// Check that the keys in $arr match the expected ones.
			$keys = [];
			switch ($arr[EXP_VISIBILITY]) {
				case 'public':
					if ($root) {
						$keys = static::$PUBLIC;
					} else {
						$keys = $arr[EXP_CLASSNAME]::$PUBLIC;
					}
					break;
				case 'private':
					if ($root) {
						$keys = static::$PRIVATE;
					} else {
						$keys = $arr[EXP_CLASSNAME]::$PRIVATE;
					}
					break;
				default:
					throw new ExportableException(
						"Unknown visibility value."
					);
			}

			$diff = arraydiff(
				$keys,
				array_diff(array_keys($arr), EXP_RESERVED)
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
		if (!$root && array_key_exists(EXP_CLASSNAME, $arr)) {
			$ret = new $arr[EXP_CLASSNAME];
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
						if (!in_array($k, EXP_RESERVED, TRUE)) {
							$ret[$k] = $v;
						}
						break;
				}
			}
		}
		return $ret;
	}
}
