<?php

namespace libresignage\common\php\util;

final class VersionNumber {
	/**
	* Construct a new VersionNumber object.
	*
	* @param array $version A version number as an array of strings.
	*/
	public function __constructor(array $version) {
		$this->version = $version;
	}

	/**
	* Parse a version string.
	*
	* Version strings are of the form:
	*
	*   vAA.BB. ... .CC(-extra)
	*
	* where AA, BB, ... are strings. Everything after a dash (-) is
	* discarded while parsing.
	*/
	public function from_string(string $version) {
		$this->version = [];
		$parts = explode('-', $version);
		if (count($parts) > 0) {
			foreach (explode('.', substr($parts[0], 1)) as $n) {
				array_push($this->version, $n);
			}
		}
	}

	/**
	* Check whether two VersionNumbers match.
	*
	* You can use an asterisk (*) as a wildcard in a version
	* number to match any value.
	*
	* @return bool TRUE if the version numbers match, FALSE otherwise.
	*/
	public function matches(VersionNumber $v): bool {
		if ($this->length() !== $v->length()) { return FALSE; }
		for ($i = 0; $i < $this->length(); $i++) {
			if ($this->get($i) === "*") {
				continue;
			} else if ($this->get($i) !== $v->get($i)) {
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	* Get a version number component by index.
	*
	* @return string|NULL The requested version number component
	*                  or NULL if not found.
	*/
	public function get(int $index) {
		if (array_key_exists($index, $this->version)) {
			return $this->version[$index];
		} else {
			return NULL;
		}
	}

	/**
	* Get the length of a version number, ie. the number of
	* individual version components.
	*
	* @return int The length of the version number.
	*/
	public function length(): int {
		return count($this->version);
	}

	/**
	* Return a string representation of a VersionNumber.
	*
	* @return string The version string.
	*/
	public function __toString(): string {
		return implode(".", $this->version);
	}
}
