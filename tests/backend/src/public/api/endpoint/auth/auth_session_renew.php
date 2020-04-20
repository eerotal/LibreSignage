<?php

namespace libresignage\tests\backend\src\pub\api\endpoint\auth;

use libresignage\tests\backend\common\classes\APITestCase;

class auth_session_renew extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('auth/auth_session_renew.php');
	}

	public function test_is_response_schema_correct() {
		$this->call_api_and_check_response_schema(
			[],
			[],
			dirname(__FILE__).'/schemas/auth_session_renew.schema.json',
			'admin',
			'admin'
		);
	}	
}
