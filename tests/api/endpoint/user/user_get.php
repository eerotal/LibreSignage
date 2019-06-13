<?php

use JsonSchema\Validator;
use JsonSchema\SchemaStorage;
use JsonSchema\Constraints\Factory;

use classes\APITestCase;
use classes\APITestUtils;

class user_get extends APITestCase {
	use traits\TestIsResponseCode200;
	use traits\TestIsResponseContentTypeJSON;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('user/user_get.php');
		$this->api->login('admin', 'admin');
	}

	public function test_is_response_output_schema_correct(): void {
		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['user' => 'admin'],
			[],
			TRUE
		);

		$schema = APITestUtils::read_json_file(
			dirname(__FILE__).'/schemas/user_get.schema.json'
		);

		$validator = new Validator();
		$validator->validate($resp, $schema);

		$this->assert_json_validator_valid($validator);
	}

	public function tearDown(): void {
		$this->api->logout();
	}
}
