<?php

namespace libresignage\common\php\exportable\diff;

use libresignage\common\php\exportable\diff\BaseDiff;

/**
* A class that describes a diff between two objects.
*/
class ObjectDiff extends ArrayDiff {
	protected $base = [];
	protected $other = [];

	public function __construct($base, $other, int $depth, bool $private) {
		parent::__construct($private);

		$this->base = get_object_vars($base);
		$this->other = get_object_vars($other);

		$this->diff($depth);
	}
}
