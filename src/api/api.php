<?php
/*
*  APIEndpoint object definition and interface functions.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

// API Endpoint request methods.
define("API_METHOD", array(
	"GET" => 0,
	"POST" => 1
));

// API Endpoint response types.
define("API_RESPONSE", array(
	"JSON" => 0,
	"TEXT" => 1
));

// API parameter bitmasks.
define("API_P_STR",		0x1);
define("API_P_INT",		0x2);
define("API_P_FLOAT",		0x4);
define("API_P_ARR",		0x8);
define("API_P_OPT",		0x10);
define("API_P_STR_ALLOW_EMPTY", 0x20);
define("API_P_NULL",		0x40);

class APIEndpoint {
	private $method = 0;
	private $response_type = 0;
	private $response = NULL;
	private $format = NULL;
	private $strict_format = TRUE;
	private $data = NULL;
	private $inited = FALSE;
	private $error = 0;

	function __construct(int $method, int $response_type,
				$format, bool $strict_format=TRUE) {
		if (!in_array($method, API_METHOD)) {
			throw new Exception('Invalid API method!');
		}
		if (!in_array($response_type, API_RESPONSE)) {
			throw new Exception('Invalid API response type!');
		}
		$this->method = $method;
		$this->format = $format;
		$this->strict_format = $strict_format;
		$this->response_type = $response_type;
	}

	private function _load_data_post() {
		/*
		*  Load POST data. Throws exception and sets the
		*  error flag on error.
		*/
		$str = @file_get_contents('php://input');
		if ($str === FALSE) {
			$this->error = API_E_INTERNAL;
			throw new Exception('Failed to read '.
					'request data!');
		}
		$data = json_decode($str, $assoc=TRUE);
		if (json_last_error() != JSON_ERROR_NONE) {
			$this->error = API_E_INTERNAL;
			throw new Exception('Request data parsing '.
						'failed!');
		}
		if (!$this->_verify($data)) {
			$this->error = API_E_INVALID_REQUEST;
			throw new Exception('Invalid request data!');
		}
		$this->data = $data;
		$this->inited = TRUE;
	}

	private function _load_data_get() {
		/*
		*  Load GET data. Throws exception and sets the
		*  error flag on error.
		*/
		if (!$this->_verify($_GET)) {
			$this->error = API_E_INVALID_REQUEST;
			throw new Exception('Invalid request data!');
		}
		$this->data = $_GET;
		$this->inited = TRUE;
	}

	function load_data() {
		/*
		*  Wrapper function for loading data into
		*  this APIEndpoint object. _load_data_post()
		*  and _load_data_get() do the actual work.
		*/
		try {
			if ($this->method == API_METHOD['POST']) {
				$this->_load_data_post();
			} else if ($this->method == API_METHOD['GET']) {
				$this->_load_data_get();
			}
		} catch(Exception $e) {
			throw $e;
		}
	}

	private function _chk_param_type($param, int $bitmask) {
		/*
		*  Check whether $param is of the type defined
		*  in $bitmask.
		*
		*  A bitmask can have a real type and NULL
		*  specified, so check for NULL first and continue
		*  to other types if NULL isn't specified as a type.
		*/
		if (API_P_NULL & $bitmask) {
			if (gettype($param) == 'NULL') {
				return TRUE;
			}
		}

		/*
		*  Check for the real types. Only one of these
		*  can be defined at a time.
		*/
		if (API_P_STR & $bitmask) {
			return gettype($param) == 'string';
		} elseif (API_P_INT & $bitmask) {
			return gettype($param) == 'integer';
		} elseif (API_P_ARR & $bitmask) {
			return gettype($param) == 'array';
		} elseif (API_P_BOOL & $bitmask) {
			return gettype($param) == 'boolean';
		} elseif (API_P_FLOAT & $bitmask) {
			return gettype($param) == 'double';
		}
	}

	private function _chk_param_data($param, int $bitmask) {
		/*
		*  Check whether the data in $param is
		*  valid according to the type defined in
		*  $bitmask.
		*/
		if (!(API_P_NULL & $bitmask) &&
			API_P_STR & $bitmask &&
			strlen($param) == 0) {
			/*
			*     Type is not NULL
			*  -> Type is string
			*  -> String length is 0
			*  -> Return TRUE if empty strings are allowed.
			*/
			return (API_P_STR_ALLOW_EMPTY & $bitmask) != 0;
		}
		return TRUE;
	}

	private function _is_param_opt(int $bitmask) {
		return (API_P_OPT & $bitmask) != 0;
	}

	private function _verify($data, $format=NULL) {
		/*
		*  Verify request data using the format filter $format
		*  or $this->format if $format === NULL. If $format
		*  and $this->format are both NULL, this function returns
		*  TRUE without doing any verification. If the flag
		*  $this->strict_format is TRUE, extra keys in $data that
		*  don't exist in format are considered invalid.
		*/
		if ($format === NULL) {
			if ($this->format === NULL) {
				return TRUE;
			}
			$format = $this->format;
		}

		// Check that each key in $format also exists in $data.
		foreach (array_keys($format) as $k) {
			if (!in_array($k, array_keys($data))) {
				if ($this->_is_param_opt($format[$k])) {
					continue;
				}
				return FALSE;
			}
			if (gettype($format[$k]) == 'array') {
				// Verify nested formats.
				if (!$this->_verify($data[$k],
						$format[$k])) {
					return FALSE;
				}
			} else {
				if (!$this->_chk_param_type($data[$k],
						$format[$k])) {
					return FALSE;
				}
				if (!$this->_chk_param_data($data[$k],
						$format[$k])) {
					return FALSE;
				}
			}
		}

		/*
		*  Consider extra keys in $data invalid if
		*  $this->strict_format is TRUE.
		*/
		if ($this->strict_format) {
			if (array_is_subset(array_keys($data),
					array_keys($format))) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		return TRUE;
	}

	function last_error() {
		/*
		*  Get the last error that occurred and
		*  reset the error flag.
		*/
		$tmp = $this->error;
		$this->error = API_E_OK;
		return $tmp;
	}

	function get($key = NULL) {
		if ($key === NULL) {
			return $this->data;
		} else {
			return $this->data[$key];
		}
	}

	function has(string $key, bool $null_check = FALSE) {
		if (in_array($key, array_keys($this->data))) {
			if ($null_check && $this->data[$key] == NULL) {
				return FALSE;
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function get_content_type() {
		switch($this->response_type) {
			case API_RESPONSE['JSON']:
				return 'application/json';
			case API_RESPONSE['TEXT']:
				return 'text/plain';
			default:
				return 'text/plain';
		}
	}

	function is_inited() {
		return $this->inited;
	}

	function resp_set(array $resp) {
		/*
		*  Set the API response data to $resp.
		*/
		$this->response = $resp;
	}

	function send() {
		/*
		*  Send the current API response.
		*/
		if (!$this->response) {
			$this->response = array();
		}
		if (!isset($this->response['error'])) {
			// Make sure the error value exists.
			$this->response['error'] = API_E_OK;
		}
		$resp_str = json_encode($this->response);
		if ($resp_str === FALSE &&
			json_last_error() != JSON_ERROR_NONE) {
			api_throw(API_E_INTERNAL);
		}
		echo $resp_str;
		exit(0);
	}
}

function api_endpoint_init(APIEndpoint $endpoint) {
	/*
	*  Initialize the APIEnpoint $endpoint and
	*  error out of the API call if an exception
	*  is thrown. This function also sets the
	*  correct HTTP Content-Type header for the
	*  endpoint.
	*/
	try {
		$endpoint->load_data();
	} catch(Exception $e) {
		api_throw($endpoint->last_error(), $e);
	}
	header('Content-Type: '.$endpoint->get_content_type());
}
