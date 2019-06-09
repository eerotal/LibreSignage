<?php

use JsonSchema\Validator;
use classes\APITestCase;
use classes\APITestUtils;

class users_list extends APITestCase {
	use traits\TestIsResponseCode200;
	use traits\TestIsResponseContentTypeJSON;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('user/users_list.php');
		$this->api->login('admin', 'admin');
	}

	public function test_is_response_output_schema_correct(): void {
		$resp = $this->api->call($this->get_endpoint_method(), $this->get_endpoint_uri(), [], [], TRUE);

		$validator = new Validator();
		$validator->validate(
			$resp,
			[
				'type' => 'object',
				'properties' => [
					'users' => [
						'type' => 'array',
						'items' => [
							'type' => 'string',
							'pattern' => '^[A-Za-z0-9_]+$'
						]
					],
					'error' => ['type' => 'integer']
				],
				'required' => ['users', 'error'],
				'additionalProperties' => FALSE
			]
		);
		$this->assertEquals(
			TRUE,
			$validator->isValid(),
			APITestUtils::json_schema_error_string($validator)
		);
	}

	public function tearDown(): void {
		$this->api->logout();
	}
}
