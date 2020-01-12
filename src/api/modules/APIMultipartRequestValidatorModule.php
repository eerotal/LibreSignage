<?php

namespace libresignage\api\modules;

use libresignage\api\APIEndpoint;
use libresignage\api\HTTPStatus;
use libresignage\api\modules\APIJSONValidatorModule;
use libresignage\common\php\Util;
use libresignage\common\php\Log;

/**
* API module for validating multipart requests. This module decodes the
* request JSON from the 'body' POST parameter and validates it against
* a JSON schema. Requests can also contain additional fields but those
* are ignored when validating.
*/
class APIMultipartRequestValidatorModule extends APIJSONValidatorModule {
	/**
	* Decode and validate a multipart HTTP request. This function
	* reads the JSON data to validate from the 'body' POST parameter.
	*
	* @see APIModule for argument and return value descriptions.
	* @see APIJsonValidatorModule::validate() for validation exceptions.
	*/
	public function run(APIEndpoint $e, array $args) {
		$this->check_args(['schema'], $args);

		$tmp = preg_match(
			':multipart/form-data; ?boundary=.+:',
			$e->get_request()->headers->get('Content-Type', '')
		);
		if ($tmp === 0) {
			throw new APIException(
				"Invalid Content-Type. Expected 'multipart/form-data'.",
				HTTPStatus::BAD_REQUEST
			);
		} else if ($tmp === FALSE) {
			throw new APIException(
				'preg_match() failed.',
				HTTPStatus::INTERNAL_SERVER_ERROR
			);
		}

		if (!$e->get_request()->request->has('body')) {
			throw new APIException(
				"POST parameter 'body' missing from request.",
				HTTPStatus::BAD_REQUEST
			);
		}

		return $this->validate(
			$e->get_request()->request->get('body'),
			$args['schema']
		);
	}
}
