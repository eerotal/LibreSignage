<?php

/*
*  An Exportable object implementation for easily exporting and importing
*  object values in a format with only primitive values, ie. no objects.
*  This is useful when object data needs to be JSON encoded/decoded for
*  example.
*/

class ExportableException extends Exception {}

const EXP_CLASSNAME = '__classname';

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
		*  Recursively export all object keys declared in static::PUBLIC
		*  when $private === FALSE. static::PRIVATE is used otherwise.
		*  If $meta === TRUE, object type metadata is included in the
		*  returned data so that Exportable::import() can restore the
		*  proper object types when importing.
		*/
		$keys = [];
		$ret = [];

		if ($private) {
			$keys = static::$PRIVATE;	
		} else {
			$keys = static::$PUBLIC;
		}

		if (in_array(EXP_CLASSNAME, $keys)) {
			throw new ExportableException(
				"Reserved name ".EXP_CLASSNAME.
				" used in Exportable object."
			);
		}

		if ($meta) { $ret[EXP_CLASSNAME] = get_class($this); }
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

	public function import(array $data) {
		/*
		*  Import object data from an array previously exported by
		*  Exportable::export(). This function can also restore proper
		*  object types if $meta was set to true when Exportable::export()
		*  was called.
		*/
		foreach ($data as $k => $v) {
			switch (gettype($v)) {
				case 'array':
					$this->__exportable_set($k, $this->imp_array($v));
					break;
				default:
					if ($k !== EXP_CLASSNAME) {
						$this->__exportable_set($k, $v);
					}
					break;
			}
		}
	}

	private function imp_array(array $arr) {
		// Handle array importing.
		$ret = [];
		if (array_key_exists(EXP_CLASSNAME, $arr)) {
			$ret = new $arr[EXP_CLASSNAME];
			$ret->import($arr);
		} else {
			foreach ($arr as $k => $v) {
				switch (gettype($v)) {
					case 'array':
						$ret[$k] = $this->imp_array($v);
						break;
					default:
						if ($k !== EXP_CLASSNAME) {
							$ret[$k] = $v;
						}
						break;
				}
			}
		}
		return $ret;
	}
}
