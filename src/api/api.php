<?php
/*
*  APIEndpoint object definition and interface functions.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/defs.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/argarray.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/api/filters/configfilter.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/filters/requestfilter.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/filters/authfilter.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/filters/ratefilter.php');

class APIEndpoint {
	// Config options.
	const METHOD               = 'method';
	const REQUEST_TYPE         = 'request_type';
	const RESPONSE_TYPE        = 'response_type';
	const FORMAT_BODY          = 'format_body';
	const FORMAT_URL           = 'format_url';
	const STRICT_FORMAT        = 'strict_format';
	const REQ_QUOTA            = 'req_quota';
	const REQ_AUTH             = 'req_auth';
	const ALLOW_COOKIE_AUTH    = 'allow_cookie_auth';

	private $method            = 0;
	private $request_type      = 0;
	private $response_type     = 0;
	private $response          = NULL;
	private $format_body       = NULL;
	private $format_url        = NULL;
	private $strict_format     = TRUE;
	private $req_quota         = TRUE;
	private $req_auth          = TRUE;
	private $data              = NULL;
	private $headers           = NULL;
	private $caller            = NULL;
	private $allow_cookie_auth = FALSE;

	public function __construct(array $config) {
		$args = new ArgumentArray(
			[
				self::METHOD            => API_METHOD,
				self::REQUEST_TYPE      => API_MIME,
				self::RESPONSE_TYPE     => API_MIME,
				self::FORMAT_BODY       => 'array',
				self::FORMAT_URL        => 'array',
				self::STRICT_FORMAT     => 'boolean',
				self::REQ_QUOTA         => 'boolean',
				self::REQ_AUTH          => 'boolean',
				self::ALLOW_COOKIE_AUTH => 'boolean'
			],
			[
				self::REQUEST_TYPE      => API_MIME['application/json'],
				self::RESPONSE_TYPE     => API_MIME['application/json'],
				self::FORMAT_BODY       => [],
				self::FORMAT_URL        => [],
				self::STRICT_FORMAT     => TRUE,
				self::REQ_QUOTA         => TRUE,
				self::REQ_AUTH          => TRUE,
				self::ALLOW_COOKIE_AUTH => FALSE
			]
		);
		$ret = $args->chk($config);
		foreach ($ret as $k => $v) { $this->$k = $v; }
	}

	public function load_headers() {
		/*
		*  Load supplied request headers;
		*/
		$this->headers = getallheaders();
	}

	public function load_data() {
		/*
		*  Load request data. What data is loaded depends on the
		*  API endpoint request method and MIME type.
		*  
		*  POST
		*    application/json
		*      * Load URL and body data into $this->data.
		*    multipart/form-data
		*      * Load URL and body data into $this->data.
		*      * Load file data into $this->files.
		*    other:
		*      * Throw an error.
		*  GET
		*    * Load URL data into $this->data.
		*  OTHER
		*    * Throw an error.
		*/
		switch($this->method) {
			case API_METHOD['POST']:
				switch ($this->request_type) {
					case API_MIME['application/json']:
						$body_raw = file_get_contents('php://input');
						$this->data = array_merge(
							$this->parse_json_request($body_raw),
							$this->parse_url_request($_GET)
						);
						break;
					case API_MIME['multipart/form-data']:
						$this->data = $this->parse_url_request($_GET);
						if (
							count($this->format_body) !== 0
							&& count($_POST) === 1
							&& array_key_exists('body', $_POST)
						) {
							$this->data = array_merge(
								$this->data,
								$this->parse_json_request($_POST['body'])
							);
						} else if (count($this->format_body) !== 0) {
							throw new ArgException(
								"Invalid multipart request data. ".
								"Missing 'body' or extra data."
							);
						}
						$this->files = $_FILES;
						break;
					default:
						throw new ArgException("Unknown request type.");
				}
				break;
			case API_METHOD['GET']:
				$this->data = $this->parse_url_request();
				break;
			default:
				throw new ArgException("Unexpected API method.");
		}
	}

	private function parse_json_request(string $str) {
		// Parse JSON request data.
		if (strlen($str) === 0) {
			$data = [];
		} else {
			$data = json_decode($str, $assoc=TRUE);
			if (
				$data === NULL &&
				json_last_error() != JSON_ERROR_NONE
			) {
				throw new IntException('JSON parsing failed!');
			}
		}
		if ($data === NULL) {
			$data = [];
 		} else if (gettype($data) !== 'array') {
			throw new ArgException(
				'Invalid request data. Expected an  '.
				'array as the root element.'
			);
		}
		$this->verify($data, $this->format_body);
		return $data;
	}

	private function parse_url_request() {
		// Parse URL request data.
		$this->verify($_GET, $this->format_url);
		return $_GET;
	}

	private function chk_arr_types(array $vals, string $type) {
		foreach ($vals as $k => $v) {
			if (gettype($v) != $type) { return FALSE; }
		}
		return TRUE;
	}

	private function chk_type(array $data, array $format, $i) {
		/*
		*  Check the value at $i in $data against the
		*  configured type flags in $format and throw an
		*  ArgException if the types don't match.
		*/
		$ok = FALSE;
		$bm = $format[$i];
		$d = $data[$i];
		$t = gettype($d);

		$ok = (
			((API_P_NULL & $bm) !== 0 && $t == 'NULL')
			|| ((API_P_STR & $bm) !== 0 && $t == 'string')
			|| ((API_P_INT & $bm) !== 0 && $t == 'integer')
			|| ((API_P_BOOL & $bm) !== 0 && $t == 'boolean')
			|| ((API_P_FLOAT & $bm) !== 0 && $t == 'double')
			|| ($t == 'array' &&
				(
					(
						(API_P_ARR_MIXED & $bm) !== 0
					) || (
						(API_P_ARR_STR & $bm) !== 0
						&& $this->chk_arr_types($d, 'string')
					) || (
						(API_P_ARR_INT & $bm) !== 0
						&& $this->chk_arr_types($d, 'integer')
					) || (
						(API_P_ARR_BOOL & $bm) !== 0
						&& $this->chk_arr_types($d, 'boolean')
					) || (
						(API_P_ARR_FLOAT & $bm) !== 0
						&& $this->chk_arr_types($d, 'double')
					)
				)
			)
		);

		if (!$ok) {
			if ($t == 'array') {
				throw new ArgException(
					"Invalid type 'array' for '$i' ".
					"or invalid array value types."
				);
			} else {
				throw new ArgException(
					"Invalid type '$t' for '$i'."
				);
			}
		}
	}

	function chk_data(array $data, array $format, $i) {
		/*
		*  Check the value at $i in $data against the
		*  configured data flags in $format and throw an
		*  ArgException id the data doesn't match the flags.
		*/
		$bitmask = $format[$i];
		$value =  $data[$i];
		if (
			!(API_P_EMPTY_STR_OK & $bitmask)
			&& gettype($data[$i]) == 'string'
			&& empty($value)
		) {
			throw new ArgException("Invalid empty data for '$i'.");
		}
	}

	private function is_param_opt(int $bitmask) {
		return (API_P_OPT & $bitmask) != 0;
	}

	private function verify($data, array $format) {
		/*
		*  Verify request data using the format filter $format.
		*  If the flag $this->strict_format is TRUE, extra keys
		*  in $data that don't exist in $format are considered invalid.
		*/
		if (count($format) === 0) {
			if ($this->strict_format) {
				return count($data) === 0;
			} else {
				return TRUE;
			}
		}

		// Check that each key in $format also exists in $data.
		foreach (array_keys($format) as $k) {
			if (!in_array($k, array_keys($data))) {
				if ($this->is_param_opt($format[$k])) { continue; }
				throw new ArgException(
					"API request parameter '$k' missing."
				);
			}
			if (gettype($format[$k]) == 'array') {
				// Verify nested formats.
				$this->verify($data[$k], $format[$k]);
			} else {
				$this->chk_type($data, $format, $k);
				$this->chk_data($data, $format, $k);
			}
		}

		/*
		*  Consider extra keys in $data invalid if
		*  $this->strict_format is TRUE.
		*/
		if ($this->strict_format) {
			if (
				!array_is_subset(
					array_keys($data),
					array_keys($format)
				)
			) {
				throw new ArgException("Extra keys in API request.");
			}
		}
	}

	public function get($key = NULL) {
		if ($key === NULL) {
			return $this->data;
		} else {
			return $this->data[$key];
		}
	}

	public function has(string $key, bool $null_check = FALSE) {
		if (array_key_exists($key, $this->data)) {
			if ($null_check && $this->data[$key] == NULL) {
				return FALSE;
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function get_header(string $key) {
		return $this->headers[$key];
	}

	public function has_header(string $key) {
		if (empty($key)) { return FALSE; }
		return array_key_exists($key, $this->headers);
	}

	public function get_response_type() { return $this->response_type; }
	public function get_request_type() { return $this->request_type; }
	public function get_method() { return $this->method; }

	public function requires_quota() { return $this->req_quota; }
	public function requires_auth() { return $this->req_auth; }
	public function allows_cookie_auth() { return $this->allow_cookie_auth; }

	public function set_caller($caller) { $this->caller = $caller; }
	public function get_caller() { return $this->caller; }

	public function set_session($session) { $this->session = $session; }
	public function get_session() { return $this->session; }

	public function get_files() { return $this->files; }

	public function resp_set($resp) {
		/*
		*  Set the API response data.
		*/
		$this->response = $resp;
	}

	public function send() {
		/*
		*  Send the current API response.
		*/
		if ($this->response_type === API_MIME['application/json']) {
			if (!$this->response) { $this->response = []; }
			if (!isset($this->response['error'])) {
				// Make sure the error value exists.
				$this->response['error'] = API_E_OK;
			}
			$resp_str = json_encode($this->response);
			if (
				$resp_str === FALSE &&
				json_last_error() !== JSON_ERROR_NONE
			) {
				throw new APIException(
					API_E_INTERNAL,
					"Failed to encode response JSON."
				);
			}
			echo $resp_str;
			exit(0);
		} else {
			if ($this->response) {
				if (
					is_resource($this->response)
					&& get_resource_type($this->response) == 'stream'
					&& stream_get_meta_data(
						$this->response
					)['stream_type'] == 'STDIO'
				) {
					fpassthru($this->response);
					fclose($this->response);
				} else {
					echo $this->response;
				}
			}
			exit(0);
		}
	}
}

function api_endpoint_init(APIEndpoint $endpoint) {
	/*
	*  Initialize the API endpoint $endpoint.
	*/
	api_error_setup();
	$endpoint->load_headers(); // Headers are needed by APIAuthFilter.
	$filters = [
		'config' => new APIConfigFilter(),
		'req' => new APIRequestFilter(),
		'auth' => new APIAuthFilter(),
		'quota' => new APIRateFilter()
	];
	foreach ($filters as $k => $f) { $f->filter($endpoint); }
	$endpoint->load_data();
}
