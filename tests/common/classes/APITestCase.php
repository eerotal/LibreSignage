<?php

namespace classes;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;

use JsonSchema\Validator;
use classes\APIInterface;

use constraints\IsAPIErrorResponse;
use constraints\APIErrorEquals;
use constraints\MatchesJSONSchema;

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

	/**
	 * Assert that the API response object $response matches the JSON Schema
	 * from the file $schema_path.
	 *
	 * @param $response mixed The API response object.
	 * @param $schema_path string The path to the JSON Schema file.
	 * @param $message string An optional error message to print when the
	 *                        assertion fails.
	 */
	public static function assert_object_matches_schema(
		$response,
		string $schema_path,
		string $message = ''
	) {
		self::assertThat(
			$response,
			new MatchesJSONSchema($schema_path),
			$message
		);
	}

	/**
	 * Assert that $response is a well-formed API error response and
	 * it's error code matches $expect. If $expect === 'API_E_OK', this
	 * function calls APITestCase::assert_api_succeeded().
	 *
	 * @param $response mixed The API response object.
	 * @param $expect string The expected API error code name.
	 * @param $message string An optional error message to print when the
	 *                        assertion fails.
	 */
	public function assert_api_failed(
		$response,
		string $expect,
		string $message = ''
	) {
		if ($expect === 'API_E_OK') {
			$this->assert_api_succeeded($response, 'API_E_OK', $message);
			return;
		}
		self::assertThat(
			$response,
			new IsAPIErrorResponse($this->api, $expect),
			$message
		);
		self::assertThat(
			$response->error,
			new APIErrorEquals($this->api, $expect),
			$message
		);
	}

	/**
	 * Assert that $response succeeded by checking that
	 * $response->error matches API_E_OK.
	 *
	 * @param $response mixed The API response object.
	 * @param $message string An optional error message to print when
	 *                        the assertion fails.
	 */
	public function assert_api_succeeded($response, string $message = '') {
		self::assertArrayHasKey(
			'error',
			(array) $response,
			'No error key in API response.'
		);
		self::assertThat(
			$response->error,
			new APIErrorEquals($this->api, 'API_E_OK'),
			$message
		);
	}
}
