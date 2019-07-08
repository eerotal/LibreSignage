<?php

use \classes\APITestCase;
use \api\HTTPStatus;

class queue_remove extends APITestCase {
	use traits\TestEndpointNotAuthorizedWithoutLogin;

	const TEST_QUEUE_NAME = 'test_queue';

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('queue/queue_remove.php');

		// Create an initial slide to remove.
		$this->api->login('admin', 'admin');
		$this->api->call(
			'POST',
			'queue/queue_create.php',
			['name' => self::TEST_QUEUE_NAME],
			[],
			TRUE
		);
		$this->api->logout();
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(
		string $user,
		string $pass,
		array $params,
		int $error
	): void {
		$this->api->login($user, $pass);

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$params,
			[],
			TRUE
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/queue_create.schema.json'
		);

		$this->api->logout();
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				'admin',
				'admin',
				['name' => self::TEST_QUEUE_NAME],
				HTTPStatus::OK
			],
			'Missing name parameter' => [
				'admin',
				'admin',
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Empty name parameter' => [
				'admin',
				'admin',
				['name' => ''],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for name paremeter' => [
				'admin',
				'admin',
				['name' => TRUE],
				HTTPStatus::BAD_REQUEST
			],
			'User display tries to remove slide of user admin' => [
				'display',
				'display',
				['name' => self::TEST_QUEUE_NAME],
				HTTPStatus::UNAUTHORIZED
			],
			'User user tries to remove slide of user admin' => [
				'user',
				'user',
				['name' => self::TEST_QUEUE_NAME],
				HTTPStatus::UNAUTHORIZED
			]
		];
	}

	public function tearDown(): void {
		// Make sure the initial slide is removed.
		$this->api->login('admin', 'admin');
		$this->api->call(
			'POST',
			'queue/queue_remove.php',
			['name' => self::TEST_QUEUE_NAME],
			[],
			TRUE
		);
		$this->api->logout();
	}
}
