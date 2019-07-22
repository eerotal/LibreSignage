<?php

namespace libresignage\tests\api\endpoint\user;

use libresignage\tests\common\classes\APITestCase;
use libresignage\api\HTTPStatus;

class user_save extends APITestCase {
	const UNIT_TEST_USER = 'unit_test_user';

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('user/user_save.php');

		// Create an initial user for testing.
		$this->api->login('admin', 'admin');
		$this->api->call(
			'POST',
			'user/user_create.php',
			[
				'user' => self::UNIT_TEST_USER,
				'groups' => []
			],
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
		$this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			$user,
			$pass
		);
	}

	public function params_provider(): array {
		return [
			'Valid parameters' => [
				'admin',
				'admin',
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => ['display']
				],
				HTTPStatus::OK
			],
			'Admin user tries to set password' => [
				'admin',
				'admin',
				[
					'user' => self::UNIT_TEST_USER,
					'pass' => 'test',
					'groups' => ['display']
				],
				HTTPStatus::UNAUTHORIZED
			],
			'Non-admin tries to set groups' => [
				'user',
				'user',
				[
					'user' => 'user',
					'groups' => ['admin']
				],
				HTTPStatus::UNAUTHORIZED
			],
			'Non-admin tries to set password' => [
				'user',
				'user',
				[
					'user' => 'user',
					'pass' => 'user'
				],
				HTTPStatus::OK
			],
			'Missing user parameter' => [
				'admin',
				'admin',
				[],
				HTTPStatus::BAD_REQUEST,
			],
			'Empty user parameter' => [
				'admin',
				'admin',
				['user' => ''],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for user parameter' => [
				'admin',
				'admin',
				['user' => 1],
				HTTPStatus::BAD_REQUEST
			],
			'Missing groups parameter' => [
				'admin',
				'admin',
				['user' => self::UNIT_TEST_USER],
				HTTPStatus::OK
			],
			'Wrong type in groups array' => [
				'admin',
				'admin',
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => [1, 2, 3]
				],
				HTTPStatus::BAD_REQUEST
			],
			'Empty string in groups array' => [
				'admin',
				'admin',
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => ['']
				],
				HTTPStatus::BAD_REQUEST
			],
			'Empty groups array' => [
				'admin',
				'admin',
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => []
				],
				HTTPStatus::OK
			]
		];
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[
				'user' => self::UNIT_TEST_USER,
				'groups' => ['editor']
			],
			[],
			TRUE
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/user_save.schema.json'
		);

		$this->api->logout();
	}

	public function tearDown(): void {
		// Remove the initial user.
		$this->api->login('admin', 'admin');
		$this->api->call(
			'POST',
			'user/user_remove.php',
			['user' => self::UNIT_TEST_USER],
			[],
			TRUE
		);
		$this->api->logout();		
	}	
}
