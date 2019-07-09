<?php

use \classes\APITestCase;
use \api\HTTPStatus;

class queue_get extends APITestCase {
	use \traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('queue/queue_get.php');
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
				['name' => 'default'],
				HTTPStatus::OK
			],
			'Missing queue name parameter' => [
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Empty queue name parameter' => [
				['name' => ''],
				HTTPStatus::BAD_REQUEST
			]
		];
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['name' => 'default'],
			[],
			TRUE
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/queue_get.schema.json'
		);

		$this->api->logout();
	}
}
