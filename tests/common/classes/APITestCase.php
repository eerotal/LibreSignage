<?php

namespace classes;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;

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

	/* ----- Assertion functions for use in tests. ----- */

	public static function assert_valid_json(
		$response,
		string $schema_path,
		string $message = ''
	) {
		/*
		*  Assert that $response properly validates against the JSON schema
		*  at $schema_path.
		*/
		$schema = APITestUtils::read_json_file($schema_path);

		$validator = new Validator();
		$validator->validate($response, $schema);
		self::assert_json_validator_valid($validator, $message);
	}

	public static function assert_json_validator_valid(
		Validator $validator,
		string $message = NULL
	) {
		/*
		*  Assert that the validator state of $validator is valid.
		*/
		if ($message === NULL) {
			$message = APITestUtils::json_schema_error_string($validator);
		}
		self::assertThat($validator->isValid(), self::isTrue(), $message);
	}

	public static function assert_api_errored(
		$response,
		int $code,
		string $message = ''
	) {
		/*
		*  Assert that $response contains a valid error response from
		*  the API. $code is the expected error code in the response.
		*/
		self::assert_valid_json(
			$response,
			SCHEMA_PATH.'/error.schema.json',
			$message
		);
		self::assertThat(
			$response->error,
			new isEqual($code),
			$message
		);
	}
}
