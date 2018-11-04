<?php
/*
*  APIEndpoint object definition and interface functions.
*  This class file contains little actual heavy lifting.
*  Most of the work is done by individual API modules in
*  the modules/ directory.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/defs.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/argarray.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module_auth.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module_configchecker.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module_dataloader.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module_validator.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module_ratelimit.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module_requestvalidator.php');

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

	private $url_data          = [];
	private $body_data         = [];
	private $file_data         = [];
	private $header_data       = [];

	private $caller            = NULL;
	private $response          = NULL;

	private $method            = 0;
	private $request_type      = 0;
	private $response_type     = 0;
	private $format_body       = NULL;
	private $format_url        = NULL;

	private $strict_format     = TRUE;
	private $req_quota         = TRUE;
	private $req_auth          = TRUE;
	private $allow_cookie_auth = FALSE;

	public function __construct(array $config) {
		api_error_setup();

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

		/*
		*  Initialize the API endpoint by executing API modules.
		*/
		$modules = [
			'config'   => new APIConfigCheckerModule(),
			'request'  => new APIRequestValidatorModule(),
			'data'     => new APIDataLoaderModule(),
			'auth'     => new APIAuthModule(),
			'rate'     => new APIRateLimitModule(),
			'val_url'  => new APIValidatorModule(
				$this->format_url,
				[$this, 'get_url_data'],
				$this->strict_format
			),
			'val_body'  => new APIValidatorModule(
				$this->format_body,
				[$this, 'get_body_data'],
				$this->strict_format
			)
		];
		foreach ($modules as $k => $m) { $m->run($this); }
	}

	public function get($key) {
		/*
		*  Get the request value $key.
		*/
		if (array_key_exists($key, $this->url_data)) {
			return $this->url_data[$key];
		} else if (array_key_exists($key, $this->body_data)) {
			return $this->body_data[$key];
		} else {
			throw new ArgException('No such key.');
		}
	}

	public function has(string $key, bool $null_check = FALSE): bool {
		/*
		*  Check whether the request value $key exists.
		*  If $null_check === TRUE, this function returns
		*  FALSE for any NULL values.
		*/
		return (
			array_key_exists($key, $this->url_data)
			&& (
				$null_check === FALSE
				|| $this->url_data[$key] !== NULL
			)
		) || (
			array_key_exists($key, $this->body_data)
			&& (
				$null_check === FALSE
				|| $this->body_data[$key] !== NULL
			)
		);
	}

	public function get_header(string $key) {
		/*
		*  Get the header $key.
		*/
		return $this->header_data[$key];
	}

	public function has_header(string $key): bool {
		/*
		*  Check whether the request header $key exists.
		*/
		return array_key_exists($key, $this->header_data);
	}

	// Data array setters.
	public function set_url_data(array $data) {
		$this->url_data = $data;
	}
	public function set_body_data(array $data) {
		$this->body_data = $data;
	}
	public function set_file_data(array $data) {
		$this->file_data = $data;
	}
	public function set_header_data(array $data) {
		$this->header_data = $data;
	}

	// Data array getters.
	public function get_url_data(): array {
		return $this->url_data;
	}
	public function get_body_data(): array {
		return $this->body_data;
	}
	public function get_file_data(): array {
		return $this->file_data;
	}
	public function get_header_data(): array {
		return $this->header_data;
	}

	// Config getters.
	public function get_response_type(): int {
		return $this->response_type;
	}
	public function get_request_type(): int {
		return $this->request_type;
	}
	public function get_method(): int {
		return $this->method;
	}

	public function requires_quota(): bool {
		return $this->req_quota;
	}
	public function requires_auth(): bool {
		return $this->req_auth;
	}
	public function allows_cookie_auth(): bool {
		return $this->allow_cookie_auth;
	}

	// Caller identification data setters/getters.
	public function set_caller($caller) {
		$this->caller = $caller;
	}
	public function get_caller() {
		return $this->caller;
	}

	public function set_session($session) {
		$this->session = $session;
	}
	public function get_session() {
		return $this->session;
	}

	public function resp_set($resp) {
		/*
		*  Set the API response data.
		*/
		$this->response = $resp;
	}

	public function send() {
		/*
		*  Send the current API response. The way the response is sent
		*  depends on the configured response type.
		*
		*  application/json
		*     - JSON encode the response and send it.
		*  libresignage/passthrough
		*     - If the response is an open file handle, read the whole
		*       file starting from the current position and send the
		*       raw data to the client. Note that you must set any
		*       headers yourself (Content-Type, Content-Length etc.).
		*  text/plain
		*     - If the response is not NULL, send it response as
		*       plaintext.
		*/
		switch($this->response_type) {
			case API_MIME['application/json']:
				if ($this->response === NULL) {
					$this->response = [];
				}
				if (!isset($this->response['error'])) {
					// Make sure the error value exists.
					$this->response['error'] = API_E_OK;
				}
				$resp_str = json_encode($this->response);
				if (
					$resp_str === FALSE
					&& json_last_error() !== JSON_ERROR_NONE
				) {
					throw new APIException(
						API_E_INTERNAL,
						"Failed to encode response JSON."
					);
				}
				echo $resp_str;
				exit(0);
			case API_MIME['libresignage/passthrough']:
				if (
					$this->response !== NULL
					&& is_resource($this->response)
					&& get_resource_type($this->response) == 'stream'
					&& stream_get_meta_data(
						$this->response
					)['stream_type'] == 'STDIO'
				) {
					fpassthru($this->response);
					fclose($this->response);
				}
				exit(0);
			case API_MIME['text/plain']:
				if ($this->response !== NULL) {
					echo $this->response;
				}
				exit(0);
			default:
				exit(1);
		}
	}
}
