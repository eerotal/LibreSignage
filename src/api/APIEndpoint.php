<?php

namespace api;

use \api\modules\APIAuthModule;
use \api\modules\APIJSONValidatorModule;
use \api\modules\APIQueryValidatorModule;
use \api\modules\APIMultipartRequestValidatorModule;
use \api\modules\APIRateLimitModule;

use \api\APIException;
use \api\HTTPStatus;
use \common\php\JSONUtils;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class APIEndpoint {
	const M_GET     = 'GET';
	const M_POST    = 'POST';
	const M_PUT     = 'PUT';
	const M_PATCH   = 'PATCH';
	const M_DELETE  = 'DELETE';
	const M_OPTIONS = 'OPTIONS';

	private $method      = NULL;
	private $request     = NULL;
	private $response    = NULL;
	private $module_data = [];

	public function __construct(array $modules, string $method, callable $hook) {
		APIException::setup();

		$ret = NULL;
		$this->method = $method;
		$this->request = Request::createFromGlobals();
		$this->response = new Response();

		// Only handle requests with the correct HTTP method.
		if ($this->method !== $this->request->getMethod()) { return; }

		// Run API modules requested by endpoint.
		foreach ($modules as $m => $args) { $this->run_module($m, $args); }

		// Run the endpoint hook function.
		$ret = $hook($this->request, $this->response, $this->module_data);

		// Send $ret as the response if it's an array or Response.
		if (is_array($ret)) {
			$this->response->headers->set('Content-Type', 'application/json');
			$this->response->setContent(JSONUtils::encode((object) $ret));
		} else if ($ret instanceof Response) {
			$this->response = $ret;
		}

		$this->send();
	}

	public function get_request(): Request { return $this->request; }
	public function get_response(): Response { return $this->response; }
	public function get_module_data(): array { return $this->module_data; }

	public function run_module(string $module, array $args) {
		try {
			$fq_module = '\\api\\modules\\'.$module;
			$this->module_data[$module] = (new $fq_module())->run($this, $args);
		} catch (IntException $e) {
			throw new APIException(
				HTTPStatus::INTERNAL_SERVER_ERROR,
				'No such API module.'
			);
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
}
