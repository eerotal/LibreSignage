<?php

require_once(LIBRESIGNAGE_ROOT.'/api/error.php');
require_once(LIBRESIGNAGE_ROOT.'/api/defs.php');

// Require all API modules.
$mods = array_diff(scandir(LIBRESIGNAGE_ROOT.'/api/modules/'), ['.', '..']);
foreach ($mods as $m) { require_once(LIBRESIGNAGE_ROOT."/api/modules/$m"); }

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class APIEndpoint {
	const M_GET     = 'GET';
	const M_POST    = 'POST';
	const M_PUT     = 'PUT';
	const M_PATCH   = 'PATCH';
	const M_DELETE  = 'DELETE';
	const M_OPTIONS = 'OPTIONS';

	private $method      = NULL;

	private $caller      = NULL;
	private $session     = NULL;

	private $request     = NULL;
	private $response    = NULL;
	private $module_data = [];

	public function __construct(array $modules, string $method, callable $hook) {
		$ret = NULL;

		api_error_setup();

		$this->method = $method;
		$this->request = Request::createFromGlobals();
		$this->response = new Response();

		// Only handle requests with the correct HTTP method.
		if ($this->method !== $this->request->getMethod()) { return; }

		// Run API modules requested by endpoint.
		foreach ($modules as $m => $args) { $this->run_module($m, $args); }

		/*
		*  Run the endpoint hook function. If the function returns an array,
		*  use it as the JSON response.
		*/
		$ret = $hook($this->request, $this->response, $this->module_data);
		if (is_array($ret)) {
			// Make sure the error code is set.
			if (!array_key_exists('error', $ret)) { $ret['error'] = API_E_OK; }

			$this->response->headers->set('Content-Type', 'application/json');
			$this->response->setContent(APIEndpoint::json_encode($ret));
		}
		$this->send();
	}

	public function get_caller(): User { return $this->caller; }
	public function get_session(): Session { return $this->session; }
	public function get_request(): Request { return $this->request; }
	public function get_response(): Response { return $this->response; }

	public function set_caller(User $caller) { $this->caller = $caller; }
	public function set_session(Session $session) { $this->session = $session; }

	public function run_module(string $module, array $args) {
		try {
			$this->module_data[$module] = (new $module())->run($this, $args);
		} catch (IntException $e) {
			throw new APIException(API_E_INTERNAL, 'No such API module.');
		}
	}

	public function send() {
		$this->response->prepare($this->request);
		$this->response->send();
	}

	/* Static functions */

	static function GET(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_GET, $hook);
	}

	static function POST(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_POST, $hook);
	}

	static function PUT(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_PUT, $hook);
	}

	static function PATCH(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_PATCH, $hook);
	}

	static function DELETE(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_DELETE, $hook);
	}

	static function OPTIONS(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_OPTIONS, $hook);
	}

	static function json_encode($data): string {
		$ret = json_encode($data);
		if ($ret === FALSE || json_last_error()	!== JSON_ERROR_NONE) {
			throw new APIException(API_E_INTERNAL, 'Failed to encode JSON.');
		}
		return $ret;
	}

	static function json_decode($data) {
		$ret = json_decode($data);
		if ($ret === NULL && json_last_error() !== JSON_ERROR_NONE) {
			throw new APIException(API_E_INTERNAL, 'Failed to decode JSON.');
		}
		return $ret;
	}
}
