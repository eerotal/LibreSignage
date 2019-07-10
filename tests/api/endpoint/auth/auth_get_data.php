<?php

use \classes\APITestCase;

class auth_get_data extends APITestCase {
	use \traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('auth/auth_get_data.php');
	}

	public function test_is_response_schema_correct(): void {
		$this->call_api_and_check_response_schema(
			[],
			[],
			dirname(__FILE__).'/schemas/auth_get_data.schema.json',
			'admin',
			'admin'
		);
	}
}
