<?php

namespace libresignage\tests\api\endpoint\auth;

use libresignage\tests\common\classes\APITestCase;
use libresignage\api\HTTPStatus;

class auth_login extends APITestCase {
	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('auth/auth_login.php');
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(array $params, int $error) {
		$this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			NULL,
			NULL
		);
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				[
					'username' => 'admin',
					'password' => 'admin',
					'who' => 'LibreSignage-Unit-Tests',
					'permanent' => FALSE
				],
				HTTPStatus::OK
			],
			'Wrong username' => [
				[
					'username' => 'wrong',
					'password' => 'admin',
					'who' => 'LibreSignage-Unit-Tests',
					'permanent' => FALSE
				],
				HTTPStatus::UNAUTHORIZED
			],
			'Wrong type for username parameter' => [
				[
					'username' => 123,
					'password' => 'admin',
					'who' => 'LibreSignage-Unit-Tests',
					'permanent' => FALSE
				],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong password' => [
				[
					'username' => 'admin',
					'password' => 'wrong',
					'who' => 'LibreSignage-Unit-Tests',
					'permanent' => FALSE
				],
				HTTPStatus::UNAUTHORIZED
			],
			'Wrong type for password parameter' => [
				[
					'username' => 'admin',
					'password' => 123,
					'who' => 'LibreSignage-Unit-Tests',
					'permanent' => FALSE
				],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong username and password' => [
				[
					'username' => 'wrong',
					'password' => 'wrong',
					'who' => 'LibreSignage-Unit-Tests',
					'permanent' => FALSE
				],
				HTTPStatus::UNAUTHORIZED
			],
			'Missing username parameter' => [
				[
					'password' => 'admin',
					'who' => 'LibreSignage-Unit-Tests',
					'permanent' => FALSE
				],
				HTTPStatus::BAD_REQUEST
			],
			'Missing password parameter' => [
				[
					'username' => 'admin',
					'who' => 'LibreSignage-Unit-Tests',
					'permanent' => FALSE
				],
				HTTPStatus::BAD_REQUEST
			],
			'Missing who parameter' => [
				[
					'username' => 'admin',
					'password' => 'admin',
					'permanent' => FALSE
				],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type who parameter' => [
				[
					'username' => 'admin',
					'password' => 'admin',
					'who' => 123,
					'permanent' => FALSE
				],
				HTTPStatus::BAD_REQUEST
			],
			'Invalid characters in who parameter' => [
				[
					'username' => 'admin',
					'password' => 'admin',
					'who' => '../../',
					'permanent' => FALSE
				],
				HTTPStatus::BAD_REQUEST
			],
			'Missing permanent parameter' => [
				[
					'username' => 'admin',
					'password' => 'admin',
					'who' => 'LibreSignage-Unit-Tests',
				],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for permanent parameter' => [
				[
					'username' => 'admin',
					'password' => 'admin',
					'who' => 'LibreSignage-Unit-Tests',
					'permanent' => 123
				],
				HTTPStatus::BAD_REQUEST
			],
		];
	}

	public function test_is_response_schema_correct() {
		$this->call_api_and_check_response_schema(
			[
				'username' => 'admin',
				'password' => 'admin',
				'who' => 'LibreSignage-Unit-Tests',
				'permanent' => FALSE
			],
			[],
			dirname(__FILE__).'/schemas/auth_login.schema.json',
			NULL,
			NULL
		);
	}

	public function tearDown(): void {
		$this->api->logout();
	}
}
