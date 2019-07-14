<?php

namespace api\modules;

use \api\APIEndpoint;
use \api\modules\APIJSONValidatorModule;
use \common\php\Util;

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
		return $this->validate(
			$e->get_request()->request->get('body'),
			$args['schema']
		);
	}
}
