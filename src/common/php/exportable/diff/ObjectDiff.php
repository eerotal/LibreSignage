<?php

namespace libresignage\common\php\exportable\diff;

use libresignage\common\php\exportable\diff\BaseDiff;

class ObjectDiff extends BaseDiff {
	private $base = NULL;
	private $other = NULL;

	public function __construct($base, $other, $private_value) {
		parent::__construct($private_value);

		$this->base = $base;
		$this->other = $other;
	}
}
