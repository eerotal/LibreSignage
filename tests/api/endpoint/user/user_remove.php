<?php

use JsonSchema\Validator;
use classes\APITestCase;
use classes\APITestUtils;

class user_remove extends APITestCase {
	use traits\TestEndpointNotAuthorizedWithoutLogin;

	const UNIT_TEST_USER = 'unit_test_user';

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('user/user_remove.php');

		// Create an initial user that the tests try to remove.
		$this->api->login('admin', 'admin');
		$this->api->call(
			'POST',
			'user/user_create.php',
			[
				'user' => self::UNIT_TEST_USER,
				'groups' => ['editor', 'display']
			],
			[],
			TRUE
		);
		$this->api->logout();
	}

	public function test_endpoint_not_authorized_for_non_admin_users(): void {
		$this->api->login('user', 'user');

		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['user' => self::UNIT_TEST_USER],
			[],
			TRUE
		);
		$this->assert_api_failed($resp, 'API_E_NOT_AUTHORIZED');

		$this->api->logout();
	}


	/*public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[],
			[],
			TRUE
		);
		$this->assert_valid_json(
			$resp,
			dirname(__FILE__).'/schemas/user_remove.schema.json'
		);

		$this->api->logout();
	}*/

	public function tearDown(): void {
		// Remove the initial user in case it wasn't successfully removed.
		$this->api->login('admin', 'admin');
		$this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['user' => self::UNIT_TEST_USER],
			[],
			TRUE
		);
		$this->api->logout();
	}
}
