<?php

use JsonSchema\Validator;
use classes\APITestCase;
use classes\APITestUtils;

class api_err_codes extends APITestCase {
	use traits\TestIsResponseCode200;
	use traits\TestIsResponseContentTypeJSON;

	public function setUp(): void {
		$this->set_endpoint_uri('general/api_err_codes.php');
		parent::setUp();
	}

	public function test_is_response_output_schema_correct(): void {
		$response = $this->client->get($this->get_endpoint_uri());
		$data = APITestUtils::json_decode((string) $response->getBody());

		$validator = new Validator();
		$validator->validate(
			$data,
			[
				'type' => 'object',
				'properties' => [
					'codes' => [
						'type' => 'object',
						'patternProperties' => [
							'^.*$' => [ 'type' => 'integer' ]
						],
						'additionalProperties' => FALSE
					],
					'error' => ['type' => 'integer']
				],
				'required' => ['codes', 'error'],
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
