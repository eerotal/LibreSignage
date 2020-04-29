<?php

namespace libresignage\tests\backend\common\constraints;

use \PHPUnit\Framework\Constraint\Constraint;
use libresignage\common\php\exportable\diff\Diff;
use libresignage\common\php\exportable\diff\BaseDiff;
use libresignage\common\php\exportable\Exportable;

class ExportableEquals extends Constraint {
	private $expect = NULL;
	private $diff = NULL;

	public function __construct(Exportable $expect) {
		$this->expect = $expect;
	}

	public function matches($other): bool {
		$this->diff = Diff::diff(
			$this->expect,
			$other,
			BaseDiff::DIFF_DEPTH_INF
		);
		return $this->diff->is_equal(FALSE);
	}

	public function failureDescription($other): string {
		return 'an Exportable object matches the expected one';
	}

	public function additionalFailureDescription($other): string {
		return "\nDiff dump included below: \n\n"
			.$this->diff->dump_str(FALSE, 0);
	}

	public function toString(): string {}
}
