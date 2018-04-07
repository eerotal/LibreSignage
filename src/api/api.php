<?php
/*
*  APIEndpoint object definition and interface functions.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/argarray.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

// API Endpoint request methods.
const API_METHOD = array(
	"GET" => 0,
	"POST" => 1
);

// API Endpoint response types.
const API_RESPONSE = array(
	"JSON" => 0,
	"TEXT" => 1
);

// API type flags.
const API_P_STR			= 0x1;
const API_P_INT			= 0x2;
const API_P_FLOAT		= 0x4;
const API_P_ARR			= 0x8;
const API_P_OPT			= 0x10;
const API_P_NULL		= 0x20;

// API data flags.
const API_P_EMPTY_STR_OK	= 0x40;

// API convenience flags.
const API_P_ANY			= API_P_STR|API_P_INT|API_P_FLOAT
				|API_P_ARR|API_P_NULL;
const API_P_UNUSED 		= API_P_ANY|API_P_EMPTY_STR_OK|API_P_OPT;

class APIEndpoint {
	// Config options.
	const METHOD		= 'method';
	const RESPONSE_TYPE	= 'response_type';
	const FORMAT		= 'format';
	const STRICT_FORMAT	= 'strict_format';
	const REQ_QUOTA		= 'req_quota';
	const REQ_API_KEY	= 'req_api_key';

	private $method = 0;
	private $response_type = 0;
	private $response = NULL;
	private $format = NULL;
	private $strict_format = TRUE;
	private $req_quota = TRUE;
	private $req_api_key = TRUE;
	private $data = NULL;
	private $inited = FALSE;
	private $caller = NULL;

	public function __construct(array $config) {
		$args = new ArgumentArray(
			array(
				self::METHOD        => API_METHOD,
				self::RESPONSE_TYPE => API_RESPONSE,
				self::FORMAT        => 'array',
				self::STRICT_FORMAT => 'boolean',
				self::REQ_QUOTA     => 'boolean',
				self::REQ_API_KEY   => 'boolean'
			),
			array(
				self::FORMAT        => array(),
				self::STRICT_FORMAT => TRUE,
				self::REQ_QUOTA     => TRUE,
				self::REQ_API_KEY   => TRUE
			)
		);
		$ret = $args->chk($config);
		foreach ($ret as $k => $v) {
			$this->$k = $v;
		}
	}

	private function _load_data_post() {
		/*
		*  Load POST data. Throws exception and sets the
		*  error flag on error.
		*/
		$str = @file_get_contents('php://input');
		if ($str === FALSE) {
			throw new IntException(
				"Failed to read request data!"
			);
		}
		if (!strlen($str)) {
			$data = array();
		} else {
			$data = json_decode($str, $assoc=TRUE);
			if ($data === NULL &&
				json_last_error() != JSON_ERROR_NONE) {
				throw new IntException(
					"Request data parsing failed!"
				);
			}
		}
		$this->_verify($data);
		$this->data = $data;
		$this->inited = TRUE;
	}

	private function _load_data_get() {
		/*
		*  Load GET data. Throws exception and sets the
		*  error flag on error.
		*/
		$this->_verify($_GET);
		$this->data = $_GET;
		$this->inited = TRUE;
	}

	public function load_data() {
		/*
		*  Wrapper function for loading data into
		*  this APIEndpoint object. _load_data_post()
		*  and _load_data_get() do the actual work.
		*/
		if ($this->method == API_METHOD['POST']) {
			$this->_load_data_post();
		} else if ($this->method == API_METHOD['GET']) {
			$this->_load_data_get();
		}
	}

	private function _chk_type(array $data, array $format, $i) {
		/*
		*  Check the value at $i in $data against the
		*  configured type flags in $format and throw an
		*  ArgException if the types don't match.
		*/
		$bitmask = $format[$i];
		$type = gettype($data[$i]);
		$ok = FALSE;

		if (API_P_NULL & $bitmask && $type == 'NULL') {
			$ok = TRUE;
		} elseif (API_P_STR & $bitmask && $type == 'string') {
			$ok = TRUE;
		} elseif (API_P_INT & $bitmask && $type == 'integer') {
			$ok = TRUE;
		} elseif (API_P_ARR & $bitmask && $type == 'array') {
			$ok = TRUE;
		} elseif (API_P_BOOL & $bitmask && $type == 'boolean') {
			$ok = TRUE;
		} elseif (API_P_FLOAT & $bitmask && $type == 'double') {
			$ok = TRUE;
		}

		if (!$ok) {
			throw new ArgException(
				"Invalid type '$type' for '$i'."
			);
		}
	}

	function _chk_data(array $data, array $format, $i) {
		/*
		*  Check the value at $i in $data against the
		*  configured data flags in $format and throw an
		*  ArgException id the data doesn't match the flags.
		*/
		$bitmask = $format[$i];
		$value =  $data[$i];

		if (!(API_P_EMPTY_STR_OK & $bitmask)
			&& gettype($data[$i]) == 'string'
			&& empty($value)) {
			throw new ArgException(
				"Invalid empty data for '$i'."
			);
		}

	}

	private function _is_param_opt(int $bitmask) {
		return (API_P_OPT & $bitmask) != 0;
	}

	private function _verify($data, array $format=array()) {
		/*
		*  Verify request data using the format filter $format
		*  or $this->format if $format is empty. If $format
		*  and $this->format are both empty, this function returns
		*  TRUE without doing any verification. If the flag
		*  $this->strict_format is TRUE, extra keys in $data that
		*  don't exist in format are considered invalid.
		*/
		if (!count($format)) {
			if (!count($this->format)) {
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
				throw new ArgException(
					"API request parameter ".
					"'$k' missing."
				);
			}
			if (gettype($format[$k]) == 'array') {
				// Verify nested formats.
				$this->_verify($data[$k], $format[$k]);
			} else {
				$this->_chk_type($data, $format, $k);
				$this->_chk_data($data, $format, $k);
			}
		}

		/*
		*  Consider extra keys in $data invalid if
		*  $this->strict_format is TRUE.
		*/
		if ($this->strict_format) {
			if (!array_is_subset(array_keys($data),
					array_keys($format))) {
				throw new ArgException(
					"Extra keys in API request."
				);
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
		if (in_array($key, array_keys($this->data))) {
			if ($null_check && $this->data[$key] == NULL) {
				return FALSE;
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function get_content_type() {
		switch($this->response_type) {
			case API_RESPONSE['JSON']:
				return 'application/json';
			case API_RESPONSE['TEXT']:
				return 'text/plain';
			default:
				return 'text/plain';
		}
	}

	public function is_inited() {
		return $this->inited;
	}

	public function requires_quota() {
		return $this->req_quota;
	}

	public function requires_api_key() {
		return $this->req_api_key;
	}

	public function set_caller($caller) {
		$this->caller = $caller;
	}

	public function get_caller() {
		return $this->caller;
	}

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
		if ($this->response_type == API_RESPONSE['TEXT']) {
			if ($this->response) {
				echo $this->response;
			}
			exit(0);
		} elseif ($this->response_type == API_RESPONSE['JSON']) {
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
				throw new APIException(
					API_E_INTERNAL,
					"Failed to encode response JSON."
				);
			}
			echo $resp_str;
			exit(0);
		}
	}
}

function api_handle_preflight() {
	/*
	*  Handle sending proper responses for preflight
	*  requests.
	*/
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, Api-Key');
	header('Access-Control-Max-Age: 600');
}

function api_handle_request(APIEndpoint $endpoint) {
	/*
	*  Handle reqular API calls using POST or GET.
	*/

	// Send required headers.
	header('Content-Type: '.$endpoint->get_content_type());
	header('Access-Control-Allow-Origin: *');

	// Initialize the endpoint.
	try {
		$endpoint->load_data();
	} catch(ArgException $e) {
		throw new APIException(
			API_E_INVALID_REQUEST, $e->getMessage(), 0, $e
		);
	} catch(IntException $e) {
		throw new APIException(
			API_E_INTERNAL, $e->getMessage(), 0, $e
		);
	}

	// Check API key if required.
	if (!$endpoint->requires_api_key()) { return; }

	if (!array_key_exists("Api-Key", getallheaders())) {
		throw new APIException(
			API_E_NOT_AUTHORIZED,
			"No Api-Key header even though required."
		);
	}

	$caller = auth_api_key_verify(getallheaders()["Api-Key"]);
	if ($caller === NULL) {
		throw new APIException(
			API_E_NOT_AUTHORIZED,
			"Invalid API key."
		);
	}
	$endpoint->set_caller($caller);

	// Use the API rate quota of the caller if required.
	if (!$endpoint->requires_quota()) { return; }

	$quota = new UserQuota($caller);
	if ($quota->has_state_var('api_t_start')) {
		$t = $quota->get_state_var('api_t_start');
		if (time() - $t >= gtlim('API_RATE_T')) {
			/*
			*  Reset rate quota and time
			*  after the cutoff.
			*/
			$quota->set_state_var('api_t_start', time());
			$quota->set_quota('api_rate', 0);
		}
	} else {
		// Start counting time.
		$quota->set_state_var('api_t_start', time());
	}

	if (!$quota->use_quota('api_rate')) {
		throw new APIException(
			API_E_RATE,
			"API rate limited."
		);
	}
	$quota->flush();
}

function api_endpoint_init(APIEndpoint $endpoint) {
	/*
	*  Handle endpoint initialization and API calls.
	*  This function and the api_handle_* functions
	*  also take care of all the smaller protocol details
	*  like handling preflight requests and sending the
	*  proper HTTP headers.
	*/

	api_error_setup();

	switch ($_SERVER['REQUEST_METHOD']) {
		case "POST":
			api_handle_request($endpoint);
			break;
		case "GET":
			api_handle_request($endpoint);
			break;
		case "OPTIONS":
			api_handle_preflight();
			break;
		default:
			header('Content-Type: '.$endpoint->get_content_type());
			throw new ArgAxception("Invalid request method.");
			break;
	}

}
