<?php

use JsonSchema\Validator;

use classes\APITestCase;
use classes\APITestUtils;

class user_get_current extends APITestCase {
	use traits\TestIsResponseCode200;
	use traits\TestIsResponseContentTypeJSON;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('user/user_get_current.php');
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');
		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[],
			[],
			TRUE
		);

		$schema = APITestUtils::read_json_file(
			dirname(__FILE__).'/schemas/user_get_current.schema.json'
		);

		$validator = new Validator();
		$validator->validate($resp, $schema);

		$this->assert_json_validator_valid($validator);

		$this->api->logout();
	}
}
