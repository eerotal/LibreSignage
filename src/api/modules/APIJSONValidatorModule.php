<?php

namespace api\modules;

use \api\APIEndpoint;
use \api\APIModule;
use \api\APIException;
use \api\HTTPStatus;
use \JsonSchema\Validator;
use \JsonSchema\Constraints\Constraint;
use \common\php\JSONUtils;
use \common\php\exceptions\JSONException;
use \common\php\Util;

/**
* API module for validating and decoding a request
* with a JSON body.
*/
class APIJSONValidatorModule extends APIModule {
	/**
	* Decode and validate the request body JSON.
	*
	* @see APIModule for argument and return value descriptions.
	*/
	public function run(APIEndpoint $e, array $args) {
		$data = NULL;
		$this->check_args(['schema'], $args);

		if ($e->get_request()->getContent() === '') {
			$data = (object) [];
		} else {
			try {
				$data = JSONUtils::decode($e->get_request()->getContent());
			} catch (JSONException $e) {
				throw new APIException(
					$e->getMessage(),
					HTTPStatus::BAD_REQUEST
				);
			}
		}

		$validator = new Validator();
		$validator->validate(
			$data,
			Util::assoc_array_to_object($args['schema']),
			Constraint::CHECK_MODE_NORMAL
		);

		if (!$validator->isValid()) {
			$err_str = "Invalid request data:\n\n";
			foreach ($validator->getErrors() as $e) {
				$err_str .= sprintf("%s: %s\n", $e['property'], $e['message']);
			}
			throw new APIException(
				$err_str,
				HTTPStatus::BAD_REQUEST
			);
		}
		return $data;
	}
}
