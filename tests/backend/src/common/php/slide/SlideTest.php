<?php

namespace libresignage\tests\backend\src\common\php\slide;

use \PHPUnit\Framework\TestCase;
use libresignage\common\php\exceptions\ArgException;
use libresignage\common\php\exceptions\IllegalOperationException;
use libresignage\common\php\Config;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\auth\User;
use libresignage\common\php\auth\Session;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;
use libresignage\common\php\slide\exceptions\SlideLockException;
use libresignage\common\php\auth\exceptions\UserNotFoundException;
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
		$this->assertThat($s, new ExportableEquals($this->slide));
	}

	public function test_generate_id(): void {
		$s = new Slide();
		$s->gen_id();
		$this->assertNotEmpty($s->get_id());
	}

	public function test_add_ref_and_get_refcount(): void {
		$s = new Slide();
		$s->add_ref();
		$this->assertEquals(1, $s->get_refcount());
	}

	public function test_remove_ref_and_get_refcount(): void {
		$s = new Slide();
		$s->add_ref();
		$s->add_ref();
		$s->remove_ref();
		$this->assertEquals(1, $s->get_refcount());
	}

	public function test_remove_ref_throws_if_one_ref_left(): void {
		$s = new Slide();
		$s->add_ref();
		$this->expectException(IllegalOperationException::class);
		$s->remove_ref();
	}

	public function test_set_and_get_id(): void {
		$s = new Slide();
		$s->set_id($this->slide->get_id());
		$this->assertEquals($this->slide->get_id(), $s->get_id());
	}

	public function test_set_id_throws_on_nonexistent_id(): void {
		$s = new Slide();
		$this->expectException(SlideNotFoundException::class);
		$s->set_id('nonexistent');
	}

	public function test_set_markup_and_get_markup(): void {
		$markup = 'aabbccdd';

		$s = new Slide();
		$s->set_markup($markup);
		$this->assertEquals($markup, $s->get_markup());
	}

	public function test_set_markup_throws_on_too_long_markup(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_markup(
			str_repeat('a', Config::limit('SLIDE_MARKUP_MAX_LEN') + 1)
		);
	}

	public function test_set_and_get_name(): void {
		$name = 'name';

		$s = new Slide();
		$s->set_name($name);
		$this->assertEquals($name, $s->get_name());
	}

	public function test_set_name_throws_on_invalid_name(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_name('.');
	}

	public function test_set_name_throws_on_too_long_name(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_name(str_repeat('a', Config::limit('SLIDE_NAME_MAX_LEN') + 1));
	}

	public function test_set_name_throws_on_empty_name(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_name('');
	}

	public function test_set_and_get_duration(): void {
		$max = Config::limit('SLIDE_MAX_DURATION');
		$min = Config::limit('SLIDE_MIN_DURATION');
		$duration_1 = ($max - $min)/2;
		$duration_2 = ($max - $min)/4;

		$s = new Slide();
		$s->set_duration($duration_1);
		$this->assertEquals($duration_1, $s->get_duration());
		$s->set_duration($duration_2);
		$this->assertEquals($duration_2, $s->get_duration());
	}

	public function test_set_duration_throws_on_too_short_duration(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_duration(Config::limit('SLIDE_MIN_DURATION') - 1);
	}

	public function test_set_duration_throws_on_too_long_duration(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_duration(Config::limit('SLIDE_MAX_DURATION') + 1);
	}

	public function test_set_and_get_owner(): void {
		$owner = 'admin';
		$s = new Slide();
		$s->set_owner($owner);
		$this->assertEquals($owner, $s->get_owner());
	}

	public function test_set_owner_throws_if_user_doesnt_exist(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_owner('nonexistent');
	}

	public function test_set_and_get_enabled(): void {
		$s = new Slide();
		$s->set_enabled(TRUE);
		$this->assertTrue($s->get_enabled());
		$s->set_enabled(FALSE);
		$this->assertFalse($s->get_enabled());
	}

	public function test_set_and_get_scheduling(): void {
		$s = new Slide();
		$s->set_sched(TRUE);
		$this->assertTrue($s->get_sched());
		$s->set_sched(FALSE);
		$this->assertFalse($s->get_sched());
	}

	public function test_set_and_get_schedule_start_time(): void {
		$time = time();

		$s = new Slide();
		$s->set_sched_t_s($time);
		$this->assertEquals($time, $s->get_sched_t_s());
	}

	public function test_set_schedule_start_time_throws_on_negative_time(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_sched_t_s(-1);
	}

	public function test_set_and_get_schedule_end_time(): void {
		$time = time();

		$s = new Slide();
		$s->set_sched_t_e($time);
		$this->assertEquals($time, $s->get_sched_t_e());
	}

	public function test_set_schedule_end_time_throws_on_negative_time(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_sched_t_e(-1);
	}

	public function test_set_schedule_end_time_throws_if_less_than_start_time(): void {
		$s = new Slide();
		$s->set_sched_t_s(1);
		$this->expectException(ArgException::class);
		$s->set_sched_t_e(0);
	}

	public function test_set_and_get_animation(): void {
		$s = new Slide();
		$s->set_animation(1);
		$this->assertEquals(1, $s->get_animation());
		$s->set_animation(2);
		$this->assertEquals(2, $s->get_animation());
	}

	public function test_set_animation_throws_on_negative_animation(): void {
		$s = new Slide();
		$this->expectException(ArgException::class);
		$s->set_animation(-1);
	}

	public function test_set_and_get_collaborators(): void {
		$collaborators = ['user', 'display'];
		$s = new Slide();
		$s->set_owner('admin'); // Owner must be set first.
		$s->set_collaborators($collaborators);
		$this->assertEquals($collaborators, $s->get_collaborators());
	}

	public function test_set_and_get_collaborators_empty_array(): void {
		$collaborators = [];
		$s = new Slide();
		$s->set_owner('admin');
		$s->set_collaborators($collaborators);
		$this->assertEquals($collaborators, $s->get_collaborators());
	}

	public function test_set_collaborators_throws_on_too_many_users(): void {
		$s = new Slide();
		$s->set_owner('admin');

		$this->expectException(ArgException::class);

		$collaborators = [];
		for ($i = 0; $i < Config::limit('SLIDE_MAX_COLLAB') + 1; $i++) {
			$collaborators[] = 'user_'.strval($i);
		}
		$s->set_collaborators($collaborators);
	}

	public function test_set_collaborators_throws_on_duplicate_users(): void {
		if (Config::limit('SLIDE_MAX_COLLAB') < 2) {
			$this->markTestSkipped(
				"Can't test with duplicate users because max collaborators < 2."
			);
		}

		$s = new Slide();
		$s->set_owner('admin');
		$this->expectException(ArgException::class);
		$s->set_collaborators(['admin', 'admin']);
	}

	public function test_set_collaborators_throws_on_nonexistent_user(): void {
		$s = new Slide();
		$s->set_owner('admin');

		$this->expectException(UserNotFoundException::class);
		$s->set_collaborators(['nonexistent']);
	}

	public function test_set_collaborators_throws_if_owner_not_set(): void {
		$s = new Slide();
		$this->expectException(IllegalOperationException::class);
		$s->set_collaborators(['admin']);
	}

	public function test_set_collaborators_throws_if_owner_is_collaborator(): void {
		$s = new Slide();
		$s->set_owner('admin');
		$this->expectException(IllegalOperationException::class);
		$s->set_collaborators(['admin']);
	}

	public function test_acquire_lock_release_lock_and_check_is_locked(): void {
		$user = new User();
		$user->load('admin');
		['session' => $session] = $user->session_new(
			'LibreSignage-Unit-Test',
			'127.0.0.1',
			FALSE
		);
		$user->write();

		$this->slide->lock_acquire($session);
		$this->assertTrue($this->slide->is_locked_by($session));
		$this->slide->lock_release($session);
		$this->assertFalse($this->slide->is_locked_by($session));
	}

	public function test_acquire_lock_throws_if_already_locked(): void {
		$user = new User();
		$user->load('admin');
		['session' => $session_1] = $user->session_new(
			'LibreSignage-Unit-Test-1',
			'127.0.0.1',
			FALSE
		);
		['session' => $session_2] = $user->session_new(
			'LibreSignage-Unit-Test-2',
			'127.0.0.1',
			FALSE
		);
		$user->write();

		$this->slide->lock_acquire($session_1);
		$this->expectException(SlideLockException::class);
		$this->slide->lock_acquire($session_2);
	}

	public function test_check_is_locked_when_not_locked(): void {
		$user = new User();
		$user->load('admin');
		['session' => $session] = $user->session_new(
			'LibreSignage-Unit-Test',
			'127.0.0.1',
			FALSE
		);
		$user->write();
		$this->assertFalse($this->slide->is_locked_by($session));
	}

	public function test_check_is_locked_when_locked_by_another_session(): void {
		$user = new User();
		$user->load('admin');
		['session' => $session_1] = $user->session_new(
			'LibreSignage-Unit-Test-1',
			'127.0.0.1',
			FALSE
		);
		['session' => $session_2] = $user->session_new(
			'LibreSignage-Unit-Test-2',
			'127.0.0.1',
			FALSE
		);
		$user->write();

		$this->slide->lock_acquire($session_1);
		$this->assertFalse($this->slide->is_locked_by($session_2));
	}

	public function test_check_is_locked_when_lock_has_expired(): void {
		$user = new User();
		$user->load('admin');
		['session' => $session] = $user->session_new(
			'LibreSignage-Unit-Test',
			'127.0.0.1',
			FALSE
		);
		$user->write();

		$this->slide->lock_acquire($session);

		// Make the lock artificially expired using Reflection.
		$ref = new \ReflectionObject($this->slide->get_lock());
		$ref_prop = $ref->getProperty('expire');
		$ref_prop->setAccessible(TRUE);
		$ref_prop->setValue($this->slide->get_lock(), 0);

		$this->assertFalse($this->slide->is_locked_by($session));
	}

	public function test_release_lock_throws_on_another_session(): void {
		$user = new User();
		$user->load('admin');
		['session' => $session_1] = $user->session_new(
			'LibreSignage-Unit-Test-1',
			'127.0.0.1',
			FALSE
		);
		['session' => $session_2] = $user->session_new(
			'LibreSignage-Unit-Test-2',
			'127.0.0.1',
			FALSE
		);
		$user->write();

		$this->slide->lock_acquire($session_1);
		$this->expectException(SlideLockException::class);
		$this->slide->lock_release($session_2);
	}

	public function test_release_lock_succeeds_when_not_locked(): void {
		$user = new User();
		$user->load('admin');
		['session' => $session] = $user->session_new(
			'LibreSignage-Unit-Test',
			'127.0.0.1',
			FALSE
		);
		$user->write();

		$this->slide->lock_release($session);

		// Make PHPUnit happy about not asserting.
		$this->addToAssertionCount(1);
	}

	public function test_clear_lock(): void {
		$user = new User();
		$user->load('admin');
		['session' => $session] = $user->session_new(
			'LibreSignage-Unit-Test',
			'127.0.0.1',
			FALSE
		);
		$user->write();

		$this->slide->lock_acquire($session);
		$this->slide->clear_lock();
		$this->assertNull($this->slide->get_lock());
	}

	public function tearDown(): void {
		$this->slide->remove();
	}
}
