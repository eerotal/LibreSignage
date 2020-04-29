<?php

namespace libresignage\tests\backend\src\common\php\exportable\diff;

use \PHPUnit\Framework\TestCase;
use libresignage\common\php\exportable\diff\EmptyDiff;

class EmptyDiffTest extends TestCase {
	public function test_always_equal(): void {
		$diff = new EmptyDiff(FALSE);
		$this->assertTrue($diff->is_equal(FALSE));

		$diff = new EmptyDiff(TRUE);
		$this->assertTrue($diff->is_equal(FALSE));

		$diff = new EmptyDiff(FALSE);
		$this->assertTrue($diff->is_equal(TRUE));

		$diff = new EmptyDiff(TRUE);
		$this->assertTrue($diff->is_equal(TRUE));
	}
}
