<?php

namespace libresignage\tests\backend\src\pub\api\endpoint\slide;

use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\APIInterface;
use libresignage\api\HTTPStatus;
use libresignage\tests\backend\common\classes\SlideUtils;
use libresignage\tests\backend\common\classes\AuthUtils;

class slide_lock_relase extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	const TEST_SLIDE_ID = '1';

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('slide/slide_lock_release.php');

		/*
		* Initially lock a slide. Don't logout because that
		* would automatically release the lock.
		*/
		$this->api->login('admin', 'admin');
		APIInterface::assert_success(SlideUtils::lock(
			$this->api,
			self::TEST_SLIDE_ID
		), 'Failed to lock testing slide.', [$this, 'abort']);
	}

	/**
	* Fuzz the parameters passed to the API endpoint.
	* This function logs in as $user, $pass if those are
	* supplied. Otherwise the existing session is used.
	*
	* @param array       $params The parameters to pass to the endpoint.
	* @param int         $error  The expected HTTP status code.
	* @param string|null $user   The username to use or NULL
	* @param string|null $pass   The password to use or NULL
	*
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(
		array $params,
		int $error,
		string $user = NULL,
		string $pass = NULL
	) {
		if ($user !== NULL && $pass !== NULL) {
			$this->api->login($user, $pass);
		}
		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$params,
			[],
			TRUE
		);
		if ($user !== NULL && $pass !== NULL) {
			$this->api->logout();
		}

		$this->assert_api_failed($resp, $error);
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				['id' => self::TEST_SLIDE_ID],
				HTTPStatus::OK
			],
			'Missing id parameter' => [
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Empty slide id' => [
				['id' => ''],
				HTTPStatus::NOT_FOUND
			],
			'Nonexistent slide id' => [
				['id' => 'aabbccddeeff'],
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for id parameter' => [
				['id' => 123],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong session tries to unlock slide' => [
				['id' => self::TEST_SLIDE_ID],
				HTTPStatus::LOCKED,
				'admin',
				'admin'
			],
		];
	}

	/**
	* Test releasing a slide lock *with the current session*.
	* Don't use APITestCase::call_api_and_check_response_schema().
	* It won't work because it creates a new session.
	*/
	public function test_is_response_schema_correct() {
		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['id' => self::TEST_SLIDE_ID],
			[],
			TRUE
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/slide_lock_release.schema.json'
		);
	}

	/**
	* Test whether slide locks are automatically released on logout.
	*/
	public function test_locks_automatically_released_on_logout() {
		$this->api->logout();

		$this->api->login('admin', 'admin');
		$resp = $this->api->call_return_raw_response(
			'GET',
			'slide/slide_get.php',
			['id' => self::TEST_SLIDE_ID],
			[],
			TRUE
		);
		$this->api->logout();

		$this->assert_api_failed($resp, HTTPStatus::OK);
		$this->assertNull(APIInterface::decode_raw_response(
			$resp
		)->slide->lock);
	}

	public function tearDown(): void {
		$this->api->login('admin', 'admin');

		// Logout the initial session.
		APIInterface::assert_success(AuthUtils::logout_other(
			$this->api
		), 'Failed to logout other sessions.', [$this->api, 'logout']);

		$this->api->logout();
	}
}
