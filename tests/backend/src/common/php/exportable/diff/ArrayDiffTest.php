<?php

namespace libresignage\tests\backend\src\common\php\exportable\diff;

use \PHPUnit\Framework\TestCase;
use libresignage\common\php\exportable\diff\ArrayDiff;
use libresignage\common\php\exportable\diff\BaseDiff;

class ArrayDiffTest extends TestCase {
	public function test_is_equal_for_non_private(): void {
		$diff = new ArrayDiff(
			['a', 'b', 'c'],
			['a', 'b', 'c'],
			BaseDiff::DIFF_DEPTH_INF,
			FALSE
		);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_is_not_equal_for_non_private(): void {
		$diff = new ArrayDiff(
			['a', 'b', 'a'],
			['a', 'b', 'c'],
			BaseDiff::DIFF_DEPTH_INF,
			FALSE
		);
		$this->assertFalse($diff->is_equal(FALSE));
	}

	public function test_is_equal_for_private_wo_compare_private(): void {
		$diff = new ArrayDiff(
			['a', 'b', 'c'],
			['a', 'b', 'c'],
			BaseDiff::DIFF_DEPTH_INF,
			TRUE
		);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_is_not_equal_for_private_wo_compare_private(): void {
		$diff = new ArrayDiff(
			['a', 'b', 'a'],
			['a', 'b', 'c'],
			BaseDiff::DIFF_DEPTH_INF,
			TRUE
		);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_is_equal_for_private_w_compare_private(): void {
		$diff = new ArrayDiff(
			['a', 'b', 'c'],
			['a', 'b', 'c'],
			BaseDiff::DIFF_DEPTH_INF,
			TRUE
		);
		$this->assertTrue($diff->is_equal(TRUE));
	}

	public function test_is_not_equal_for_private_w_compare_private(): void {
		$diff = new ArrayDiff(
			['a', 'b', 'a'],
			['a', 'b', 'c'],
			BaseDiff::DIFF_DEPTH_INF,
			TRUE
		);
		$this->assertFalse($diff->is_equal(TRUE));
	}

	public function test_always_equal_if_recursion_depth_is_one(): void {
		$diff = new ArrayDiff(
			['a', 'b', 'a'],
			['a', 'b', 'c'],
			1,
			FALSE
		);
		$this->assertTrue($diff->is_equal(FALSE));
	}
}
