<?php

namespace libresignage\common\php\exportable\migration;

use libresignage\common\php\exportable\migration\exceptions\MigrationException;

/**
* A class representing an entry in a MigrationIndex.
*/
final class MigrationIndexEntry {
	/**
	* Construct a new MigrationIndexEntry.
	*
	* @param string $from      The origin version string.
	* @param string $to        The result version string.
	* @param string $fqcn      The fully-qualified classname of the
	*                          transformation class.
	* @param string $data_fqcn The fully-qualified classname of the data class.
	*/
	public function __construct(
		string $from,
		string $to,
		string $fqcn,
		string $data_fqcn
	) {
		$this->from = $from;
		$this->to = $to;
		$this->fqcn = $fqcn;
		$this->data_fqcn = $data_fqcn;
	}

	/**
	* Test whether a MigrationIndexEntry transforms data of a class from
	* a specific version to a newer one.
	*
	* @param string $fqcn The fully-qualified classname of the data class.
	* @param string $from The origin version to test for.
	*
	* @return bool TRUE if the MigrationIndexEntry transforms data from
	*              the requested version, FALSE otherwise.
	*/
	public function transforms(string $fqcn, string $from): bool {
		if ($this->data_fqcn !== $fqcn) { return FALSE; }

		$a = explode(".", $from);
		$b = explode(".", $this->from);

		if (count($a) !== count($b)) {
			throw new MigrationException(
				"Version numbers must have the same number of components!"
			);
		}

		for ($i = 0; $i < count($b); $i++) {
			if ($b[$i] === "*") {
				continue;
			} else if ($b[$i] !== $a[$i]) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	* Get the result version string.
	*
	* @return string The version string.
	*/
	public function get_result_version(): string {
		return $this->to;
	}

	/**
	* Get the FQCN of the transformation class.
	*
	* @return string The transform class FQCN.
	*/
	public function get_fqcn(): string {
		return $this->fqcn;
	}
}
