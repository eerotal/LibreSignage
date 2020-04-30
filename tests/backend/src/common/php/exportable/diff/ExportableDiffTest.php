<?php

namespace libresignage\tests\backend\src\common\php\exportable\diff;

use \PHPUnit\Framework\TestCase;
use libresignage\common\php\exportable\diff\ExportableDiff;
use libresignage\common\php\exportable\diff\BaseDiff;
use libresignage\tests\backend\common\classes\ExportableTestClass;

class ExportableDiffTest extends TestCase {
	public function test_private_and_non_private_members_equal(): void {
		$a = new ExportableTestClass();
		$a->set_a('a');
		$a->set_b('b');
		$b = new ExportableTestClass();
		$b->set_a('a');
		$b->set_b('b');

		$diff = new ExportableDiff($a, $b, BaseDiff::DIFF_DEPTH_INF, FALSE);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_non_private_members_not_equal(): void {
		$a = new ExportableTestClass();
		$a->set_a('a');
		$a->set_b('b');
		$b = new ExportableTestClass();
		$b->set_a('b');
		$b->set_b('b');

		$diff = new ExportableDiff($a, $b, BaseDiff::DIFF_DEPTH_INF, FALSE);
		$this->assertFalse($diff->is_equal(FALSE));
	}

	public function test_private_members_not_equal_wo_compare_private(): void {
		$a = new ExportableTestClass();
		$a->set_a('a');
		$a->set_b('b');
		$b = new ExportableTestClass();
		$b->set_a('a');
		$b->set_b('a');

		$diff = new ExportableDiff($a, $b, BaseDiff::DIFF_DEPTH_INF, FALSE);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_private_members_not_equal_w_compare_private(): void {
		$a = new ExportableTestClass();
		$a->set_a('a');
		$a->set_b('b');
		$b = new ExportableTestClass();
		$b->set_a('a');
		$b->set_b('a');

		$diff = new ExportableDiff($a, $b, BaseDiff::DIFF_DEPTH_INF, FALSE);
		$this->assertFalse($diff->is_equal(TRUE));
	}

	public function test_always_equal_if_recursion_depth_is_one(): void {
		$a = new ExportableTestClass();
		$a->set_a('a');
		$a->set_b('b');
		$b = new ExportableTestClass();
		$b->set_a('c');
		$b->set_b('d');

		$diff = new ExportableDiff($a, $b, 1, FALSE);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_non_private_members_equal_recursive_obj(): void {
		$a = new ExportableTestClass();
		$a->set_a(1);
		$a->set_b(2);
		$b = new ExportableTestClass();
		$b->set_a($a);
		$b->set_b(10);

		$c = new ExportableTestClass();
		$c->set_a(1);
		$c->set_b(2);
		$d = new ExportableTestClass();
		$d->set_a($c);
		$d->set_b(10);

		$diff = new ExportableDiff($a, $c, BaseDiff::DIFF_DEPTH_INF, FALSE);
		$this->assertTrue($diff->is_equal(FALSE));
	}

	public function test_non_private_members_not_equal_recursive_obj(): void {
		$a = new ExportableTestClass();
		$a->set_a('right');
		$a->set_b(2);
		$b = new ExportableTestClass();
		$b->set_a($a);
		$b->set_b(10);

		$c = new ExportableTestClass();
		$c->set_a('wrong');
		$c->set_b(2);
		$d = new ExportableTestClass();
		$d->set_a($c);
		$d->set_b(10);

		$diff = new ExportableDiff($a, $c, BaseDiff::DIFF_DEPTH_INF, FALSE);
		$this->assertFalse($diff->is_equal(FALSE));
	}

	public function test_stop_on_maximum_recursion_depth(): void {
		$a = new ExportableTestClass();
		$b = new ExportableTestClass();
		$c = new ExportableTestClass();

		$d = new ExportableTestClass();
		$e = new ExportableTestClass();
		$f = new ExportableTestClass();

		$a->set_a($b);
		$b->set_a($c);
		$c->set_a('right');

		$d->set_a($e);
		$e->set_a($f);
		$f->set_a('wrong'); // Different but outside of max recursion depth.

		$diff = new ExportableDiff($a, $d, 2, FALSE);
		$this->assertTrue($diff->is_equal(FALSE));
	}
}
