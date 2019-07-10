<?php

use \classes\APITestCase;

class auth_logout extends APITestCase {
	use \traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('auth/auth_logout.php');
	}

	public function test_is_response_schema_correct() {
		$this->call_api_and_check_response_schema(
			[],
			[],
			dirname(__FILE__).'/schemas/auth_logout.schema.json',
			'admin',
			'admin'
		);
	}
}
