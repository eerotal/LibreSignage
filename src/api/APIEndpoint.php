<?php

namespace libresignage\api;

use libresignage\api\modules\APIAuthModule;
use libresignage\api\modules\APIJSONValidatorModule;
use libresignage\api\modules\APIQueryValidatorModule;
use libresignage\api\modules\APIMultipartRequestValidatorModule;
use libresignage\api\modules\APIRateLimitModule;

use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\JSONUtils;

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
	private $request     = NULL;
	private $response    = NULL;
	private $module_data = [];

	/**
	* Construct the an APIEndpoint object.
	*
	* @param array    $modules An array of classnames to use for the APIEndpoint.
	* @param string   $method  The HTTP method of the endpoint in uppercase.
	* @param callable $hook    The worker function of the endpoint.
	*/
	public function __construct(array $modules, string $method, callable $hook) {
		APIException::setup();

		$ret = NULL;
		$this->method = $method;
		$this->request = Request::createFromGlobals();
		$this->response = new Response();

		if ($this->method !== $this->request->getMethod()) { return; }

		foreach ($modules as $m => $args) { $this->run_module($m, $args); }

		$ret = $hook($this->request, $this->module_data);

		// Send $ret as the response if it's an array or Response.
		if (is_array($ret)) {
			$this->response->headers->set('Content-Type', 'application/json');
			$this->response->setContent(JSONUtils::encode((object) $ret));
		} else if ($ret instanceof Response) {
			$this->response = $ret;
		}

		$this->send();
	}

	/**
	* Run a module on an APIEndpoint.
	*
	* @param string $module The classname of the module.
	* @param array  $args   Arguments to pass to the module.
	*
	* @throws APIException if the module is not found.
	*/
	public function run_module(string $module, array $args) {
		try {
			$fq_module = 'libresignage\\api\\modules\\'.$module;
			$this->module_data[$module] = (new $fq_module())->run($this, $args);
		} catch (IntException $e) {
			throw new APIException(
				HTTPStatus::INTERNAL_SERVER_ERROR,
				'No such API module.'
			);
		}
	}

	/**
	* Send the APIEndpoint response.
	*/
	private function send() {
		$this->response->prepare($this->request);
		$this->response->send();
	}

	public function get_request(): Request { return $this->request; }
	public function get_module_data(): array { return $this->module_data; }

	/**
	* Construct a GET APIEndpoint.
	*
	* @param array    $modules API modules to use for the endpoint.
	* @param callable $hook    The worker function of the endpoint.
	*/
	static function GET(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_GET, $hook);
	}

	/**
	* Construct a POST APIEndpoint.
	*
	* @param array    $modules API modules to use for the endpoint.
	* @param callable $hook    The worker function of the endpoint.
	*/
	static function POST(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_POST, $hook);
	}

	/**
	* Construct a PUT APIEndpoint.
	*
	* @param array    $modules API modules to use for the endpoint.
	* @param callable $hook    The worker function of the endpoint.
	*/
	static function PUT(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_PUT, $hook);
	}

	/**
	* Construct a PATCH APIEndpoint.
	*
	* @param array    $modules API modules to use for the endpoint.
	* @param callable $hook    The worker function of the endpoint.
	*/
	static function PATCH(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_PATCH, $hook);
	}

	/**
	* Construct a DELETE APIEndpoint.
	*
	* @param array    $modules API modules to use for the endpoint.
	* @param callable $hook    The worker function of the endpoint.
	*/
	static function DELETE(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_DELETE, $hook);
	}

	/**
	* Construct a OPTIONS APIEndpoint.
	*
	* @param array    $modules API modules to use for the endpoint.
	* @param callable $hook    The worker function of the endpoint.
	*/
	static function OPTIONS(array $modules, callable $hook) {
		return new APIEndpoint($modules, APIEndpoint::M_OPTIONS, $hook);
	}
}
