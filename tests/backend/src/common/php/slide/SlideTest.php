<?php

namespace libresignage\tests\backend\src\common\php\slide;

use \PHPUnit\Framework\TestCase;
use libresignage\common\php\slide\Slide;
use libresignage\tests\backend\common\constraints\ExportableEquals;

class SlideTest extends TestCase {
	const TEST_SLIDE_NAME = 'test-slide';

	public function setUp(): void {
		$this->slide = new Slide();
		$this->slide->gen_id();
		$this->slide->set_name(self::TEST_SLIDE_NAME);
		$this->slide->set_owner('admin');
		$this->slide->set_collaborators(['user', 'display']);
		$this->slide->write();
	}

	public function test_load_slide(): void {
		$s = new Slide();
		$s->load($this->slide->get_id());
		$s->set_name('testtest');
		$this->assertThat($s, new ExportableEquals($this->slide));
	}

	public function tearDown(): void {
		
	}
}
