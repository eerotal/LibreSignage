<?php

namespace libresignage\tests\backend\src\common\php\Queue;

use \PHPUnit\Framework\TestCase;
use libresignage\common\php\queue\Queue;
use libresignage\common\php\Config;
use libresignage\common\php\queue\exceptions\QueueNotFoundException;

class QueueTest extends TestCase {
	private $queue = NULL;

	public function setUp(): void {
		$this->queue = new Queue();
	}

	public function test_load(): void {
		$this->queue->load('default');
		$this->assertSame('default', $this->queue->get_name());
		$this->assertSame('admin', $this->queue->get_owner());
		$this->assertNotEmpty($this->queue->get_slides());
	}

	public function tearDown(): void {}
}
