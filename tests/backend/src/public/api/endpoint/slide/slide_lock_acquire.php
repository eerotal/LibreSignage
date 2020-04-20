<?php

namespace libresignage\tests\backend\src\pub\api\endpoint\slide;

use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\APIInterface;
use libresignage\api\HTTPStatus;
use libresignage\tests\backend\common\classes\SlideUtils;
use libresignage\tests\backend\common\classes\AuthUtils;

class slide_lock_acquire extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	const TEST_SLIDE_ID = '1';

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('slide/slide_lock_acquire.php');
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(
		string $user,
		string $pass,
		array $params,
		int $error
	) {
		$this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			$user,
			$pass
		);
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				'admin',
				'admin',
				['id' => self::TEST_SLIDE_ID],
				HTTPStatus::OK
			],
			'Empty slide id' => [
				'admin',
				'admin',
				['id' => ''],
				HTTPStatus::NOT_FOUND
			],
			'Missing id parameter' => [
				'admin',
				'admin',
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Nonexistent slide id' => [
				'admin',
				'admin',
				['id' => 'aabbccddeeff'],
				HTTPStatus::NOT_FOUND
			],
			'User user tries to lock slide of user admin' => [
				'user',
				'user',
				['id' => self::TEST_SLIDE_ID],
				HTTPStatus::UNAUTHORIZED
			]
		];
	}

	public function test_is_response_schema_correct() {
		$this->call_api_and_check_response_schema(
			['id' => self::TEST_SLIDE_ID],
			[],
			dirname(__FILE__).'/schemas/slide_lock_acquire.schema.json',
			'admin',
			'admin'
		);
	}

	/**
	* Test that two session can't own a lock on a slide simultaneously
	* or that a session can't override a lock owned by another session.
	*/
	public function test_no_locking_on_already_locked_slides() {
		$resp = NULL;
		for ($i = 0; $i < 2; $i++) {
			$this->api->login('admin', 'admin');
			$resp = $this->api->call_return_raw_response(
				'POST',
				'slide/slide_lock_acquire.php',
				['id' => self::TEST_SLIDE_ID],
				[],
				TRUE
			);
		}
		$this->assert_api_failed($resp, HTTPStatus::LOCKED);

		APIInterface::assert_success(AuthUtils::logout_other(
			$this->api
		), 'Failed to logout other sessions.', [$this->api, 'logout']);

		$this->api->logout();
	}
}
