<?php

namespace classes;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use classes\APITestUtils;
use classes\APIInterfaceException;

final class APIInterface {
	private $client = NULL;
	private $session_token = NULL;
	private $error_codes = [];
	private $error_messages = [];

	public function __construct(string $host) {
		$this->client = new Client(['base_uri' => $host]);

		// Load error codes and messages.
		$codes = $this->call(
			'GET',
			'/api/endpoint/general/api_err_codes.php',
			[],
			[],
			FALSE
		);
		if (!property_exists($codes, 'error') || $codes->error !== 0) {
			throw new APIInterfaceException('Failed to load API error codes.');
		}

		$msgs = $this->call(
			'GET',
			'/api/endpoint/general/api_err_msgs.php',
			[],
			[],
			FALSE
		);
		if (!property_exists($msgs, 'error') || $msgs->error !== 0) {
			throw new APIInterfaceException('Failed to load API error messages.');
		}

		foreach ((array) $codes->codes as $name => $code) {
			$this->error_codes[$name] = $code;
			$this->error_messages[$name] = (array) $msgs->messages[$code];
		}
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
			try {
				return APITestUtils::json_decode((string) $resp->getBody());
			} catch (Exception $e) {
				throw new APIInterfaceException(
					'Malformed JSON response received from API.'
				);
			}
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
		if ($use_auth && !empty($this->session_token)) {
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

	public function get_error_code(string $name) {
		return $this->error_codes[$name];
	}

	public function get_error_message_short(string $name) {
		return $this->error_messages[$name]['short'];
	}

	public function get_error_message_long(string $name) {
		return $this->error_messages[$name]['long'];
	}
}
