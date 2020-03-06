<?php

namespace libresignage\tests\common\classes;

use \GuzzleHttp\Client;
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;
use \Psr\Http\Message\StreamInterface;

use libresignage\tests\common\classes\APITestUtils;
use libresignage\tests\common\classes\APIInterfaceException;
use libresignage\tests\common\classes\APISession;

use libresignage\common\php\JSONUtils;
use libresignage\common\php\exceptions\JSONException;
use libresignage\api\HTTPStatus;

final class APIInterface {
	private $client = NULL;
	private $sessions = [];

	/**
	* Construct the APIInterface object.
	*
	* @param string $host             The hostname to use for the Guzzle client.
	* @param bool   $dump_zombie_info Whether to dump information about zombie
	*                                 sessions when the script exits.
	*/
	public function __construct(string $host, bool $dump_zombie_info = TRUE) {
		$this->client = new Client([
			'base_uri' => $host,
			'http_errors' => FALSE
		]);

		if ($dump_zombie_info) {
			register_shutdown_function([$this, 'report_zombie_sessions']);
		}
	}

	/**
	* Dump a summary of all zombie sessions.
	*/
	public function report_zombie_sessions() {
		if (count($this->sessions) !== 0) {
			foreach ($this->sessions as $s) {
				echo "Session {$s->get_username()}: {$s->get_token()} created at:\n";
				echo $s->get_backtrace()."\n\n";
			}
		}
	}

	/**
	* A wrapper for APIInterface::call_return_raw_response() that
	* decodes the response.
	*
	* @see APIInterface::call_return_raw_response()
	*/
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

	/*
	* Call an API endpoint and return the raw Response object.
	*
	* @param string   $method           The HTTP method to use.
	* @param string   $url              The URL of the API endpoint.
	* @param mixed    $data             The data to send to the endpoint.
	* @param array    $headers          The headers to send to the endpoint.
	* @param bool     $use_auth         Whether to use authentication.
	* @param callable $fallback_encoder A function to use for encoding the
	*                                   body data when a suitable function
	*                                   is not found.
	*
	* @return Response The API response.
	*/
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
		if (!empty($this->sessions)) {
			return $req->withHeader(
				'Auth-Token',
				$this->get_session()->get_token()
			);
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
		$fallback = NULL
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
				if (
					!$resp->hasHeader('Content-Length')
					|| (int) $resp->getHeader('Content-Length')[0] === 0
				) {
					return (object) [];
				} else {
					try {
						return JSONUtils::decode((string) $resp->getBody());
					} catch (JSONException $e) {
						throw new APIInterfaceException(
							'Malformed JSON response received from API.'
						);
					}
				}
			default;
				return $fallback($resp->getBody());
		}
	}

	/**
	* Assert that an API call succeeded.
	*
	* @param Response $resp    The API response.
	* @param string   $message An optional message to print if the call failed.
	* @param callable $cleanup An optional cleanup hook to run before throwing
	*                          an exception. The exception object thrown is
	*                          passed as the first argument.
	*
	* @return Response The original response object.
	*
	* @throws Exception if the call failed.
	*/
	public static function assert_success(
		Response $resp,
		string $message = NULL,
		callable $cleanup = NULL
	): Response {
		if ($resp->getStatusCode() !== HTTPStatus::OK) {
			$rdump = APITestUtils::pretty_print(
				APIInterface::decode_raw_response($resp)
			);

			$e = new \Exception(
				!empty($message)
					? $message.
						"\n\nAPI call failed. Response:\n".
						$rdump.
						"\n\n"
					: "API call failed."
			);
			if ($cleanup !== NULL) { ($cleanup)($e); }
			throw $e;
		}
		return $resp;
	}

	/**
	* Pop all sessions of a user from the internal sessions array.
	* You should use this function after manually calling auth_logout_other.php.
	*
	* @param string $user The username to match.
	*/
	public function pop_sessions_of(string $user) {
		$this->sessions = array_values(array_filter(
			$this->sessions,
			function(APISession $s) use ($user) {
				return ($s->get_username() !== $user);
			}
		));
	}

	/**
	* Add an APISession object to the internal sessions array.
	*
	* @param APISession $session The APISession object to add.
	*/
	public function add_session(APISession $session) {
		$this->sessions[] = $session;
	}

	/**
	* Pop sessions from the internal sessions array.
	*
	* @param int $count The number of sessions to pop. Default = 1.
	*/
	public function pop_session(int $count = 1) {
		for ($i = 0; $i < $count; $i++) { array_pop($this->sessions); }
	}

	/**
	* Get a session by its index in $this->sessions. If $n === NULL,
	* the last session is returned.
	*
	* @param int $n The index of the session to get.
	*/
	public function get_session(int $n = NULL) {
		return ($n === NULL) ? end($this->sessions) : $this->sessions[$n];
	}

	/**
	* Log in to the API. This function always creates a permanent session.
	*
	* @param string $user            The username to use.
	* @param string $pass            The password to use.
	*
	* @throws APIInterfaceException if the login fails.
	*/
	public function login(
		string $user,
		string $pass
	) {
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
			$this->add_session(new APISession(
				$user,
				$decoded->token
			));
		} else {
			throw new APIInterfaceException(
				"Login failed. ({$raw->getStatusCode()})"
			);
		}
	}

	/**
	* Log out from the API.
	*/
	public function logout() {
		$this->call(
			'POST',
			'auth/auth_logout.php',
			[],
			[],
			TRUE
		);
		$this->pop_session();
	}
}
