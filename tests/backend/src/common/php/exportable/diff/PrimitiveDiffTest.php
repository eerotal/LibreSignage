<?php

namespace libresignage\tests\backend\src\common\php\exportable\diff;

use \PHPUnit\Framework\TestCase;
use libresignage\common\php\exportable\diff\PrimitiveDiff;
use libresignage\common\php\exportable\diff\BaseDiff;

class PrimitiveDiffTest extends TestCase {
	public function test_is_equal_for_non_private(): void {
		$diff = new PrimitiveDiff('a', 'b', FALSE);
		$this->assertFalse($diff->is_equal(FALSE));
	}

	public function test_is_not_equal_for_non_private(): void {
		$diff = new PrimitiveDiff('a', 'a', FALSE);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_is_equal_for_private_wo_compare_private(): void {
		$diff = new PrimitiveDiff('a', 'a', TRUE);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_is_not_equal_for_private_wo_compare_private(): void {
		$diff = new PrimitiveDiff('a', 'b', TRUE);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_is_equal_for_private_w_compare_private(): void {
		$diff = new PrimitiveDiff('a', 'a', TRUE);
		$this->assertTrue($diff->is_equal(TRUE));
	}

	public function test_is_not_equal_for_private_w_compare_private(): void {
		$diff = new PrimitiveDiff('a', 'b', TRUE);
		$this->assertFalse($diff->is_equal(TRUE));
	}
}
