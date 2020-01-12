<?php

namespace libresignage\common\php\exportable;

/**
* A class for passing additional data of an Exportable to
* various functions.
*
* This object can hold various information such as the filepath
* of the exportable etc.
*/
final class ExportableDataContext {
	const FILEPATH = "filepath";
	const CLASSNAME = "classname";

	public function __constructor(array $data) {
		$this->data = $data;
	}

	/**
	* Get one of the data items added to a context.
	*
	* @param string $key The item to get.
	*/
	public function get(string $key) {
		return $this->data[$key];
	}

	/**
	* Set a data item of a context.
	*
	* @param string $key The item to set.
	*
	* @return mixed The value of the item.
	*/
	public function set(string $key, $value) {
		$this->data[$key] = $value;
	}

	/**
	* Check whether a key is defined.
	*
	* @return bool TRUE if the key exists, FALSE otherwise.
	*/
	public function has(string $key) {
		return array_key_exists($key, $this->data);
	}
}
