<?php

namespace classes;

use GuzzleHttp\Psr7\Response;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;

use JsonSchema\Validator;
use classes\APIInterface;

use constraints\IsAPIErrorResponse;
use constraints\HTTPStatusEquals;
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
	 * it's status code matches $expect. If $expect === 200, this
	 * function calls APITestCase::assert_api_succeeded().
	 *
	 * @param $response Response The HttpFoundation Response object.
	 * @param $expect string The expected HTTP status code.
	 * @param $message string An optional error message to print when the
	 *                        assertion fails.
	 */
	public function assert_api_failed(
		Response $response,
		int $expect,
		string $message = ''
	) {
		if ($expect === 200) {
			$this->assert_api_succeeded($response, $message);
			return;
		}
		self::assertThat(
			APIInterface::decode_raw_response($response),
			new IsAPIErrorResponse($this->api, $expect),
			$message
		);
		self::assertThat(
			$response->getStatusCode(),
			new HTTPStatusEquals($this->api, $expect),
			$message
		);
	}

	/**
	 * Assert that $response succeeded by checking that
	 * $response->error matches 200.
	 *
	 * @param $response mixed The API response object.
	 * @param $message string An optional error message to print when
	 *                        the assertion fails.
	 */
	public function assert_api_succeeded(
		Response $response,
		string $message = ''
	) {
		self::assertThat(
			$response->getStatusCode(),
			new HTTPStatusEquals($this->api, 200),
			$message
		);
	}
}
