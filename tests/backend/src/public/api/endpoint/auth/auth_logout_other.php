<?php

namespace libresignage\tests\backend\src\pub\api\endpoint\auth;

use libresignage\tests\backend\common\classes\APITestCase;

class auth_logout_other extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('auth/auth_logout_other.php');
	}

	public function test_is_response_schema_correct() {
		$this->call_api_and_check_response_schema(
			[],
			[],
			dirname(__FILE__).'/schemas/auth_logout_other.schema.json',
			'admin',
			'admin'
		);
	}

	public function test_removes_all_other_sessions() {
		$this->api->login('admin', 'admin');
		$this->api->login('admin', 'admin');
		$this->api->login('admin', 'admin');

		$this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[],
			[],
			TRUE
		);

		$sessions = $this->api->call(
			'GET',
			'auth/auth_get_sessions.php',
			[],
			[],
			TRUE
		);

		$this->assertCount(
			1,
			$sessions->sessions,
			"Too many sessions after calling auth_logout_other.php."
		);

		$this->api->logout();

		// Properly keep track of zombie sessions.
		$this->api->pop_sessions_of('admin');
	}
}
