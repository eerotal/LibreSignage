<?php

namespace libresignage\tests\backend\common\classes;

use \GuzzleHttp\Psr7\Response;

use \PHPUnit\Framework\TestCase;
use \PHPUnit\Framework\Constraint\IsEqual;

use \JsonSchema\Validator;
use libresignage\tests\backend\common\classes\APIInterface;

use libresignage\tests\backend\common\constraints\IsAPIErrorResponse;
use libresignage\tests\backend\common\constraints\HTTPStatusEquals;
use libresignage\tests\backend\common\constraints\MatchesJSONSchema;
use libresignage\tests\backend\common\constraints\HeaderEquals;
use libresignage\tests\backend\common\constraints\HeaderExists;

use libresignage\api\HTTPStatus;

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
	* Abort a test. This function also logs out of the API.
	*
	* @param Throwable $e (optional) An explanatory exception object.
	*/
	public function abort(\Throwable $e = NULL) {
		$this->api->logout();
		$this->markTestSkipped($e !== NULL ? (string) $e : 'Aborted.');
	}

	/**
	* Assert that a Response has a header.
	*
	* @param Response $response The Response object to check.
	* @param string   $header   The expected header.
	* @param string   $message  An optional error message to print when
	*                           the assertion fails.
	*/
	public static function assert_header_exists(
		Response $response,
		string $header,
		string $message = ''
	) {
		self::assertThat(
			$response,
			new HeaderExists($header),
			$message
		);
	}

	/**
	* Assert that a Response header matches a specific header.
	*
	* @param Response $response The Response object to check.
	* @param string   $key      The header name.
	* @param array    $value    The expected header value(s).
	* @param callable $matcher  A matcher function for HeaderEquals.
	*                           @see HeaderEquals::__construct()
	* @param string   $message  An optional error message to print when
	*                           the assertion fails.
	*/
	public static function assert_header_matches(
		Response $response,
		string $key,
		array $value,
		callable $matcher = NULL,
		string $message = ''
	) {
		self::assertThat(
			$response,
			new HeaderEquals($key, $value, $matcher),
			$message
		);
	}

	/**
	* Assert that the API response object $response matches the JSON Schema
	* from the file $schema_path.
	*
	* @param mixed  $response     The API response object.
	* @param string $schema_path  The path to the JSON Schema file.
	* @param string $message      An optional error message to print when the
	*                             assertion fails.
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
	* it's status code matches $expect. If $expect === HTTPStatus::OK,
	* this function calls APITestCase::assert_api_succeeded().
	*
	* @param Response $response The HttpFoundation Response object.
	* @param string   $expect   The expected HTTP status code.
	* @param string   $message  An optional error message to print when the
	*                           assertion fails.
	*/
	public static function assert_api_failed(
		Response $response,
		int $expect,
		string $message = ''
	) {
		if ($expect === HTTPStatus::OK) {
			self::assert_api_succeeded($response, $message);
			return;
		}
		self::assertThat(
			APIInterface::decode_raw_response($response),
			new IsAPIErrorResponse($expect),
			$message
		);
		self::assertThat(
			$response,
			new HTTPStatusEquals($expect),
			$message
		);
	}

	/**
	* Assert that $response succeeded by checking that
	* $response->error matches HTTPStatus::OK.
	*
	* @param mixed  $response The API response object.
	* @param string $message  An optional error message to print when
	*                         the assertion fails.
	*/
	public static function assert_api_succeeded(
		Response $response,
		string $message = ''
	) {
		self::assertThat(
			$response,
			new HTTPStatusEquals(HTTPStatus::OK),
			$message
		);
	}

	/**
	* Call the configured API endpoint and assert that the call failed. The
	* endpoint that's called is set using APITestCase::set_endpoint_method()
	* and APITestCase::set_endpoint_uri().
	*
	* If you pass the $user and $pass arguments, this function also
	* authenticates to the API using the provided credentials.
	*
	* @see APITestCase::assert_api_failed() (The wrapped function.)
	*
	* @param array  $params  The parameters to pass to the API endpoint.
	* @param array  $headers The headers to pass to the API endpoint.
	* @param int    $error   The expected HTTP status code.
	* @param string $user    The username to use or NULL for no login.
	* @param string $pass    The password to use or NULL for no login. 
	*
	* @return Response The response returned by the API.
	*/
	public function call_api_and_assert_failed(
		array $params,
		array $headers,
		int $error,
		string $user = NULL,
		string $pass = NULL
	) {
		if ($user !== NULL && $pass !== NULL) {
			$this->api->login($user, $pass);
		}
		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$params,
			$headers,
			$user !== NULL && $pass !== NULL
		);
		if ($user !== NULL && $pass !== NULL) {
			$this->api->logout();
		}

		$this->assert_api_failed($resp, $error);

		return $resp;
	}

	/**
	* Call the configured API endpoint and assert that the response
	* schema matches the schema loaded from $schema_path. The endpoint
	* that's called is set using APITestCase::set_endpoint_method() and
	* APITestCase::set_endpoint_uri().
	*
	* If you pass the $user and $pass arguments, this function also
	* authenticates to the API using the provided credentials.
	*
	* @see APITestCase::assert_object_matches_schema() (The wrapped function.)
	*
	* @param array  $params       The parameters to pass to the API endpoint.
	* @param array  $headers      The headers to pass to the API endpoint.
	* @param string $schema_path  The path to the expected response schema.
	* @param string $user         The username to use or NULL for no login.
	* @param string $pass         The password to use or NULL for no login. 
	*
	* @return Response The response returned by the API.
	*/
	public function call_api_and_check_response_schema(
		array $params,
		array $headers,
		string $schema_path,
		string $user = NULL,
		string $pass = NULL
	) {
		if ($user !== NULL && $pass !== NULL) {
			$this->api->login($user, $pass);
		}
		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$params,
			$headers,
			$user !== NULL && $pass !== NULL
		);
		if ($user !== NULL && $pass !== NULL) {
			$this->api->logout();
		}

		self::assert_object_matches_schema(
			APIInterface::decode_raw_response($resp),
			$schema_path
		);

		return $resp;
	}
}
