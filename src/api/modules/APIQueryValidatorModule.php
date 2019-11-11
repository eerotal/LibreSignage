<?php

namespace libresignage\api\modules;

use libresignage\api\APIEndpoint;
use libresignage\api\APIModule;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\Util;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

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
			Util::assoc_array_to_object($args['schema']),
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
