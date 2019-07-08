<?php

namespace api\modules;

use \api\APIEndpoint;
use \api\APIModule;
use \api\APIException;
use \api\HTTPStatus;
use \JsonSchema\Validator;
use \JsonSchema\Constraints\Constraint;

/**
* API module for validating a GET query.
*/
class APIQueryValidatorModule extends APIModule {
	/**
	* Validate the query.
	*
	* @see APIModule for argument and return value descriptions.
	*/
	public function run(APIEndpoint $e, array $args) {
		$this->check_args(['schema'], $args);

		$data = (object) $e->get_request()->query->all();

		$validator = new Validator();
		$validator->validate(
			$data,
			$args['schema'],
			(
				Constraint::CHECK_MODE_COERCE_TYPES
				| Constraint::CHECK_MODE_APPLY_DEFAULTS
			)
		);

		if (!$validator->isValid()) {
			$err_str = "Invalid query data:\n\n";
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
