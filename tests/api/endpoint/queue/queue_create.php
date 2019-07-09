<?php

use \classes\APITestCase;
use \api\HTTPStatus;

class queue_create extends APITestCase {
	use traits\TestEndpointNotAuthorizedWithoutLogin;

	const TEST_QUEUE_NAME = 'test_queue';

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('queue/queue_create.php');
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(array $params, int $error): void {
		$this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			'admin',
			'admin'
		);
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				['name' => self::TEST_QUEUE_NAME],
				HTTPStatus::OK
			],
			'Missing name parameter' => [
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Empty name parameter' => [
				['name' => ''],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for name paremeter' => [
				['name' => TRUE],
				HTTPStatus::BAD_REQUEST
			]
		];
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['name' => self::TEST_QUEUE_NAME],
			[]
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/queue_create.schema.json'
		);

		$this->api->logout();
	}

	public function tearDown(): void {
		$this->api->logout();
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
