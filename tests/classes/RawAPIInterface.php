<?php

namespace classes;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use classes\APITestUtils;

final class RawAPIInterface {
	private $client = NULL;
	private $session_token = NULL;

	public function __construct(string $host) {
		$this->client = new Client(['base_uri' => $host]);
	}

	public function call(
		string $method,
		string $url,
		array $data = [],
		array $headers = [],
		bool $use_auth = FALSE
	) {
		$resp = $this->call_return_raw_response($method, $url, $data, $headers, $use_auth);

		// Decode the response body if Content-Type is application/json.
		if ($resp->getHeader('Content-Type')[0] === 'application/json') {
			return APITestUtils::json_decode((string) $resp->getBody());
		} else {
			return $resp->getBody();
		}
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
				$body = APITestUtils::json_encode($data);
			}
		}

		// Set the default request content type.
		if ($method === 'POST' && empty($headers['Content-Type'])) {
			$headers['Content-Type'] = 'application/json';
		}

		// Pass session token in the Auth-Token header.
		if ($use_auth) {
			if (empty($this->session_token)) {
				throw new Exception("No session token, can't authenticate.");
			}
			$headers['Auth-Token'] = $this->session_token;
		}

		$req = new Request($method, $url, $headers, $body);
		return $this->client->send($req);
	}

	public function login(string $user, string $pass) {
		$response = $this->call(
			'POST',
			'auth/auth_login.php',
			[
				'username' => $user,
				'password' => $pass,
				'who' => 'PHPUnit',
				'permanent' => TRUE
			]
		);
		$this->session_token = $response->session->token;
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
