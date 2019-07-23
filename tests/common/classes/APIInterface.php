<?php

namespace libresignage\tests\common\classes;

use \GuzzleHttp\Client;
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;
use \Psr\Http\Message\StreamInterface;

use libresignage\tests\common\classes\APITestUtils;
use libresignage\tests\common\classes\APIInterfaceException;

use libresignage\common\php\JSONUtils;
use libresignage\common\php\exceptions\JSONException;
use libresignage\api\HTTPStatus;

final class APIInterface {
	private $client = NULL;
	private $session_token = NULL;

	public function __construct(string $host) {
		$this->client = new Client([
			'base_uri' => $host,
			'http_errors' => FALSE
		]);
	}

	public function call(
		string $method,
		string $url,
		$data,
		array $headers = [],
		bool $use_auth = FALSE,
		callable $fallback_encoder = NULL
	) {
		$resp = $this->call_return_raw_response(
			$method,
			$url,
			$data,
			$headers,
			$use_auth,
			$fallback_encoder
		);
		return APIInterface::decode_raw_response($resp);
	}

	public function call_return_raw_response(
		string $method,
		string $url,
		$data,
		array $headers = [],
		bool $use_auth = FALSE,
		callable $fallback_encoder = NULL
	): Response {
		$req = APIInterface::encode_raw_request(
			$method,
			$url,
			$data,
			$headers,
			$fallback_encoder
		);
		if ($use_auth) { $req = $this->authenticate_request($req); }
		return $this->client->send($req);
	}

	/**
	* Add the current authentication token to a Request. If
	* the API is not authenticated, no action is taken.
	*
	* @param Request $req The Request object to use.
	*
	* @return Request The modified Request.
	*/
	public function authenticate_request(Request $req): Request {
		if (!empty($this->session_token)) {
			return $req->withHeader('Auth-Token', $this->session_token);
		} else {
			return $req;
		}
	}

	/**
	* Encode raw request data into a Request object.
	*
	* @param string   $method     The HTTP method to use.
	* @param string   $url        The API endpoint URL.
	* @param mized    $data       The data to send to the endpoint.
	* @param array    $headers    And associative array of headers.
	* @param callable $fallback   A fallback function to use for encoding for
	*                             datatypes other than string, array, object,
	*                             NULL and StreamInterface. The default is
	*                             strval().
	*
	* @return Request The created Request object.
	*/
	public static function encode_raw_request(
		string $method,
		string $url,
		$data,
		array $headers,
		callable $fallback = NULL
	): Request {
		$body = NULL;
		$fallback = ($fallback === NULL) ? 'strval' : $fallback;

		if (
			is_array($data)
			|| is_object($data)
			&& !($data instanceof StreamInterface)
		) {
			if ($method === 'GET') {
				$url .= '?'.http_build_query($data);
			} else {
				if (empty($headers['Content-Type'])) {
					$headers['Content-Type'] = 'application/json';
				}
				$body = JSONUtils::encode($data);
			}
		} else if (is_string($data)) {
			if (empty($headers['Content-Type'])) {
				$headers['Content-Type'] = 'text/plain';
			}
			$body = $data;
		} else if ($data instanceof StreamInterface) {
			if ($method === 'GET') {
				throw new APIInterfaceException(
					"Can't send data streams with a GET request."
				);
			}
			$body = $data;
		} else if ($data === NULL) {
			$body = '';
		} else {
			$body = $fallback($data);
		}

		return new Request($method, $url, $headers, $body);
	}

	/**
	* Decode raw response data into a suitable datatype.
	*
	* @param Response $resp The Response object to use.
	* @param callable $fallback A fallback decoder function to use for	
	*                           mimetypes other than application/json.
	*                           The default is strval().
	*
	* @throws APIInterfaceException if the response is malformed JSON.
	*
	* @return mixed The decoded response data.
	*/
	public static function decode_raw_response(
		Response $resp,
		callable $fallback = NULL
	) {
		if ($fallback === NULL) { $fallback = 'strval'; }

		switch ($resp->getHeader('Content-Type')[0]) {
			case 'application/json':
				if ((int) $resp->getHeader('Content-Length')[0] === 0) {
					return [];
				} else {
					try {
						return JSONUtils::decode((string) $resp->getBody());
					} catch (JSONException $e) {
						var_dump($resp->getBody()->getContents());
						throw new APIInterfaceException(
							'Malformed JSON response received from API.'
						);
					}
				}
			default;
				return $fallback($resp->getBody());
		}
	}

	public function login(string $user, string $pass) {
		$raw = $this->call_return_raw_response(
			'POST',
			'auth/auth_login.php',
			[
				'username' => $user,
				'password' => $pass,
				'who' => 'PHPUnit',
				'permanent' => TRUE
			]
		);
		$decoded = APIInterface::decode_raw_response($raw);

		if ($raw->getStatusCode() === HTTPStatus::OK) {
			$this->session_token = $decoded->token;
		} else {
			throw new APIInterfaceException(
				"Login failed. ({$raw->getStatusCode()})"
			);
		}
	}

	public function logout() {
		$this->call(
			'POST',
			'auth/auth_logout.php',
			[],
			[],
			TRUE
		);
	}
}
