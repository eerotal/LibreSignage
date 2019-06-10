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
}
