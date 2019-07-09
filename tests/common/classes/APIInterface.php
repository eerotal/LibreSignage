<?php

namespace classes;

use \GuzzleHttp\Client;
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;
use \classes\APITestUtils;
use \classes\APIInterfaceException;

use \common\php\JSONUtils;
use \api\HTTPStatus;

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
		array $data = [],
		array $headers = [],
		bool $use_auth = FALSE
	) {
		$resp = $this->call_return_raw_response(
			$method,
			$url,
			$data,
			$headers,
			$use_auth
		);
		return APIInterface::decode_raw_response($resp);
	}

	public function call_return_raw_response(
		string $method,
		string $url,
		array $data = [],
		array $headers = [],
		bool $use_auth = FALSE
	): Response {
		$body = NULL;
		$req = NULL;

		// Pass request data in URL or body.
		if (!empty($data)) {
			if ($method === 'GET') {
				$url .= '?'.\http_build_query($data);
			} else {
				$body = JSONUtils::encode($data);
			}
		}

		// Set the default request content type.
		if ($method === 'POST' && empty($headers['Content-Type'])) {
			$headers['Content-Type'] = 'application/json';
		}

		// Pass session token in the Auth-Token header.
		if ($use_auth && !empty($this->session_token)) {
			$headers['Auth-Token'] = $this->session_token;
		}

		$req = new Request($method, $url, $headers, $body);
		return $this->client->send($req);
	}

	public static function decode_raw_response(Response $resp) {
		if ($resp->getHeader('Content-Type')[0] === 'application/json') {
			// Decode the response body if Content-Type is application/json.
			try {
				return JSONUtils::decode((string) $resp->getBody());
			} catch (Exception $e) {
				throw new APIInterfaceException(
					'Malformed JSON response received from API.'
				);
			}
		} else {
			// Otherwise return it as a string.
			return (string) $resp->getBody();
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
