<?php

use JsonSchema\Validator;
use classes\APITestCase;
use classes\APITestUtils;

class api_err_msgs extends APITestCase {
	use traits\TestIsResponseCode200;
	use traits\TestIsResponseContentTypeJSON;

	public function test_is_response_output_schema_correct(): void {
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
