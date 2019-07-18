<?php

namespace api\endpoint\user;

use \JsonSchema\Validator;
use \classes\APITestCase;
use \classes\APITestUtils;
use \api\HTTPStatus;

class user_get extends APITestCase {
	use \traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('user/user_get.php');
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['user' => 'admin'],
			[],
			TRUE
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/user_get.schema.json'
		);

		$this->api->logout();
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(
		array $params,
		int $error
	): void {
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
				['user' => 'admin'],
				HTTPStatus::OK
			],
			'Missing user parameter' => [
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Nonexistent user' => [
				['user' => 'nouser'],
				HTTPStatus::BAD_REQUEST
			],
			'Empty user parameter' => [
				['user' => ''],
				HTTPStatus::BAD_REQUEST
			]
		];
	}

	public function test_endpoint_not_authorized_for_non_admin_users(): void {
		$this->api->login('user', 'user');

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['user' => 'admin'],
			[],
			TRUE
		);		
		$this->assert_api_failed($resp, 401);

		$this->api->logout();
	}
}
