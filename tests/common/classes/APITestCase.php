<?php

namespace classes;

use PHPUnit\Framework\TestCase;
use JsonSchema\Validator;
use classes\APIInterface;

class APITestCase extends TestCase {
	public $api = NULL;
	private $endpoint_uri = NULL;
	private $endpoint_method = NULL;

	public function setUp(): void {
		$host = getenv('PHPUNIT_API_HOST', TRUE);
		assert(!empty($host), "'PHPUNIT_API_HOST' env variable not set.");

		$this->api = new APIInterface($host.'/api/endpoint/');
	}

	public function set_endpoint_uri(string $uri) {
		$this->endpoint_uri = $uri;
	}

	public function set_endpoint_method(string $method) {
		$this->endpoint_method = $method;
	}

	public function get_endpoint_uri(): string {
		return $this->endpoint_uri;
	}

	public function get_endpoint_method(): string {
		return $this->endpoint_method;
	}

	/* Assertion functions for use in tests. */

	public function assert_json_validator_valid(Validator $validator) {
		/*
		*  Assert that the validator state of $validator is valid.
		*/
		$this->assertEquals(
			TRUE,
			$validator->isValid(),
			APITestUtils::json_schema_error_string($validator)
		);
	}

	public function assert_api_errored($response, int $code) {
		/*
		*  Assert that $response contains a valid error response from
		*  the API. $code is the expected error code in the response.
		*/
		$schema = APITestUtils::read_json_file(SCHEMA_PATH.'/error.schema.json');
		$schema->definitions->error->enum = [$code];

		$validator = new Validator();
		$validator->validate($response, $schema);

		$this->assert_json_validator_valid($validator);
	}
}
