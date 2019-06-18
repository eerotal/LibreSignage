<?php

use classes\APITestCase;

class user_save extends APITestCase {
	const UNIT_TEST_USER = 'unit_test_user';
	const UNIT_TEST_PASS = 'unit_test_pass';

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('user/user_save.php');

		// Create an initial user for testing.
		$this->api->login('admin', 'admin');
		$pass = $this->api->call(
			'POST',
			'user/user_create.php',
			[
				'user' => self::UNIT_TEST_USER,
				'groups' => []
			],
			[],
			TRUE
		)->pass;
		$this->api->logout();

		$this->api->login(self::UNIT_TEST_USER, $pass);
		$this->api->call(
			'POST',
			'user/user_save.php',
			[
				'user' => self::UNIT_TEST_USER,
				'pass' => self::UNIT_TEST_PASS,
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
		string $error
	) {
		$this->api->login($user, $pass);

		$resp =	$this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$params,
			[],
			TRUE
		);
		$this->assert_api_failed($resp, $error);

		$this->api->logout();
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
				'API_E_OK'
			],
			'Admin user tries to set password' => [
				'admin',
				'admin',
				[
					'user' => self::UNIT_TEST_USER,
					'pass' => 'test',
					'groups' => ['display']
				],
				'API_E_NOT_AUTHORIZED'
			],
			'Non-admin tries to set groups' => [
				self::UNIT_TEST_USER,
				self::UNIT_TEST_PASS,
				[
					'user' => 'user',
					'groups' => ['admin']
				],
				'API_E_NOT_AUTHORIZED'
			],
			'Non-admin tries to set password' => [
				self::UNIT_TEST_USER,
				self::UNIT_TEST_PASS,
				[
					'user' => self::UNIT_TEST_USER,
					'pass' => 'test'
				],
				'API_E_OK'
			],
			'Missing user parameter' => [
				'admin',
				'admin',
				[],
				'API_E_INVALID_REQUEST'
			],
			'Empty user parameter' => [
				'admin',
				'admin',
				['user' => ''],
				'API_E_INVALID_REQUEST'
			],
			'Wrong type for user parameter' => [
				'admin',
				'admin',
				['user' => 1],
				'API_E_INVALID_REQUEST'
			],
			'Missing groups parameter' => [
				'admin',
				'admin',
				['user' => self::UNIT_TEST_USER],
				'API_E_OK'
			],
			'Wrong type in groups array' => [
				'admin',
				'admin',
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => [1, 2, 3]
				],
				'API_E_INVALID_REQUEST'
			],
			'Empty string in groups array' => [
				'admin',
				'admin',
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => ['']
				],
				'API_E_INVALID_REQUEST'
			],
			'Empty groups array' => [
				'admin',
				'admin',
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => []
				],
				'API_E_INVALID_REQUEST'
			],
		];
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
