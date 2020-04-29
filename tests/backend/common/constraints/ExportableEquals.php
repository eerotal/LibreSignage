<?php

namespace libresignage\tests\backend\common\constraints;

use \PHPUnit\Framework\Constraint\Constraint;
use libresignage\common\php\exportable\diff\ExportableDiff;
use libresignage\common\php\exportable\Exportable;

class ExportableEquals extends Constraint {
	private $expect = NULL;
	private $diff = NULL;

	public function __construct(Exportable $expect) {
		$this->expect = $expect;
	}

	public function matches($other): bool {
		$this->diff = new ExportableDiff(
			$this->expect,
			$other,
			ExportableDiff::DIFF_DEPTH_INF
		);
		return $this->diff->is_equal(FALSE);
	}

	public function failureDescription($other): string {
		return 'an Exportable object matches the expected one';
	}

	public function additionalFailureDescription($other): string {
		return "\nExportableDiff dump included below: \n\n"
			.$this->diff->dump_str(FALSE, 0);
	}

	public function toString(): string {}
}
