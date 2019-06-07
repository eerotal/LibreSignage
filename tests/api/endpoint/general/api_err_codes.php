<?php

use JsonSchema\Validator;

class APIErrCodesTest extends APITestCase {
	public function test_response_HTTP_code(): void {
		$response = $this->client->get('general/api_err_codes.php');
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function test_response_content_type(): void {
		$response = $this->client->get('general/api_err_codes.php');
		$this->assertEquals(
			TRUE,
			$response->hasHeader('Content-Type')
		);
		$this->assertEquals(
			'application/json',
			$response->getHeader('Content-Type')[0]
		);
	}

	public function test_response_output_schema(): void {
		$response = $this->client->get('general/api_err_codes.php');
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
					]
				]
			]
		);
		$this->assertEquals(
			TRUE,
			$validator->isValid(),
			APITestUtils::json_schema_error_string($validator)
		);
	}
}
