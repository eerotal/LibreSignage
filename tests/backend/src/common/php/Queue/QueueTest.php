<?php

namespace libresignage\tests\backend\src\common\php\Queue;

use \PHPUnit\Framework\TestCase;
use libresignage\common\php\queue\Queue;
use libresignage\common\php\Config;
use libresignage\common\php\queue\exceptions\QueueNotFoundException;
use libresignage\common\php\exceptions\ArgException;
use libresignage\common\php\slide\Slide;

class QueueTest extends TestCase {
	const TEST_QUEUE_NAME = 'test-queue';
	const TEST_QUEUE_OWNER = 'admin';
	private $queue = NULL;

	public function setUp(): void {
		$this->queue = new Queue();
		$this->queue->set_name(self::TEST_QUEUE_NAME);
		$this->queue->set_owner(self::TEST_QUEUE_OWNER);
		$this->queue->write();
	}

	public function test_load_queue(): void {
		$q = new Queue();
		$q->load(self::TEST_QUEUE_NAME);
		$this->assertSame(self::TEST_QUEUE_NAME, $q->get_name());
		$this->assertSame('admin', $q->get_owner());
		$this->assertEmpty($q->get_slides());
	}

	public function test_load_nonexistent(): void {
		$this->expectException(QueueNotFoundException::class);
		$q = new Queue();
		$q->load('nonexistent');
	}

	public function test_get_name(): void {
		$this->assertSame(self::TEST_QUEUE_NAME, $this->queue->get_name());
	}

	public function test_get_owner(): void {
		$this->assertSame(self::TEST_QUEUE_OWNER, $this->queue->get_owner());
	}

	public function test_set_name(): void {
		$this->queue->set_name('test-queue-2');
		$this->assertSame('test-queue-2', $this->queue->get_name());

		// Set the name back to the original so Queue removal succeeds.
		// TODO: Fix this in the backend code.
		$this->queue->set_name(self::TEST_QUEUE_NAME);
	}

	public function test_set_empty_name(): void {
		$this->expectException(ArgException::class);
		$this->queue->set_name('');
	}

	public function test_set_too_long_name(): void {
		$this->expectException(ArgException::class);
		$this->queue->set_name(
			str_repeat(
				'a',
				Config::limit('QUEUE_NAME_MAX_LEN') + 1
			)
		);
	}

	public function test_set_name_with_invalid_chars(): void {
		$this->expectException(ArgException::class);
		$this->queue->set_name('test.queue');
	}

	public function test_set_owner(): void {
		$this->queue->set_owner('user');
		$this->assertSame('user', $this->queue->get_owner());
	}

	public function test_set_empty_owner(): void {
		$this->expectException(ArgException::class);
		$this->queue->set_owner('');
	}

	public function test_set_too_long_owner(): void {
		$this->expectException(ArgException::class);
		$this->queue->set_owner(
			str_repeat(
				'a',
				Config::limit('USERNAME_MAX_LEN') + 1
			)
		);
	}

	public function test_set_owner_with_invalid_chars(): void {
		$this->expectException(ArgException::class);
		$this->queue->set_name('test.owner');
	}

	public function test_write_queue(): void {
		$this->queue->set_owner('user');
		$this->queue->write();

		$q = new Queue;
		$q->load(self::TEST_QUEUE_NAME);
		$this->assertSame($q->get_owner(), 'user');
	}

	public function test_add_slide_and_get_slides(): void {
		$s = new Slide();
		$s->gen_id();

		$this->queue->add_slide($s, 0);
		$this->assertSame([$s], $this->queue->get_slides());
	}

	public function test_add_slide_as_last(): void {
		$s1 = new Slide();
		$s1->gen_id();
		$s2 = new Slide();
		$s2->gen_id();

		$this->queue->add_slide($s1, 0);
		$this->queue->add_slide($s2, Queue::ENDPOS);

		$this->assertSame([$s1, $s2], $this->queue->get_slides());
	}

	public function test_add_slide_as_first(): void {
		$s1 = new Slide();
		$s1->gen_id();
		$s2 = new Slide();
		$s2->gen_id();

		$this->queue->add_slide($s1, 0);
		$this->queue->add_slide($s2, 0);

		$this->assertSame([$s2, $s1], $this->queue->get_slides());
	}

	public function test_add_slide_between_two_slides(): void {
		$s1 = new Slide();
		$s1->gen_id();
		$s2 = new Slide();
		$s2->gen_id();
		$s3 = new Slide();
		$s3->gen_id();

		$this->queue->add_slide($s1, 0);
		$this->queue->add_slide($s2, Queue::ENDPOS);
		$this->queue->add_slide($s3, 1);

		$this->assertSame([$s1, $s3, $s2], $this->queue->get_slides());
	}

	public function test_get_slide_by_id(): void {
		$s = new Slide();
		$s->gen_id();

		$this->queue->add_slide($s, 0);
		$this->assertSame($s, $this->queue->get_slide($s->get_id()));
	}

	public function test_get_nonexistent_slide(): void {
		$this->assertNull($this->queue->get_slide('aaaaa'));
	}

	public function test_get_index(): void {
		$s1 = new Slide();
		$s1->gen_id();
		$s2 = new Slide();
		$s2->gen_id();

		$this->queue->add_slide($s1, 0);
		$this->queue->add_slide($s2, Queue::ENDPOS);

		$this->assertSame(0, $this->queue->get_index($s1));
		$this->assertSame(1, $this->queue->get_index($s2));
	}

	public function test_get_index_of_nonexistent_slide(): void {
		$s = new Slide();
		$s->gen_id();

		$this->assertSame(Queue::NPOS, $this->queue->get_index($s));
	}

	public function test_get_last_slide(): void {
		$s1 = new Slide();
		$s1->gen_id();
		$s2 = new Slide();
		$s2->gen_id();

		$this->queue->add_slide($s1, 0);
		$this->queue->add_slide($s2, 1);

		$this->assertSame($s2, $this->queue->get_last_slide());
	}

	public function test_existence_check(): void {
		$this->assertTrue(Queue::exists(self::TEST_QUEUE_NAME));
	}

	public function test_list_queue_names(): void {
		$this->assertContains(self::TEST_QUEUE_NAME, Queue::list());
	}

	public function tearDown(): void {
		$this->queue->remove();
	}
}
