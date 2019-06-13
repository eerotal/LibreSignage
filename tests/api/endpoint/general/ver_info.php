<?php

use JsonSchema\Validator;
use classes\APITestCase;
use classes\APITestUtils;

class ver_info extends APITestCase {
	use traits\TestIsResponseCode200;
	use traits\TestIsResponseContentTypeJSON;

	public function setUp(): void {
		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('general/ver_info.php');
		parent::setUp();
	}

	public function test_is_response_schema_correct(): void {
		$resp = $this->api->call($this->get_endpoint_method(), $this->get_endpoint_uri());

		$validator = new Validator();
		$validator->validate(
			$resp,
			[
				'type' => 'object',
				'properties' => [
					'ls' => ['type' => 'string'],
					'api' => ['type' => 'string'],
					'error' => ['type' => 'integer']
				],
				'required' => ['ls', 'api', 'error'],
				'additionalProperties' => FALSE
			]
		);
		$this->assertEquals(
			TRUE,
			$validator->isValid(),
			APITestUtils::json_schema_error_string($validator)
		);
	}
}
