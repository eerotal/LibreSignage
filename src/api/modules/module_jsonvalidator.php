<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/module.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/auth/auth.php');

class APIJsonValidatorModule extends APIModule {
	public function __construct() {
		parent::__construct();
	}

	public function run(APIEndpoint $e, array $args) {
		$this->check_args(['schema'], $args);

		$data = APIEndpoint::json_decode($e->get_request()->getContent());

		$validator = new JsonSchema\Validator();
		$validator->validate($data, $args['schema']);

		if (!$validator->isValid()) {
			$err_str = "Invalid request data:\n\n";
			foreach ($validator->getErrors() as $e) {
				$err_str .= sprintf("%s: %s\n", $e['property'], $e['message']);
			}
			throw new APIException(API_E_INVALID_REQUEST, $err_str);
		}

		/*
		*  Cast $data back to an array because JsonSchema\Validator converts
		*  the data fed to it into an object for some reason.
		*/
		return (array) $data;
	}
}
