<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIModule.php');

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use common\php\JSONUtils;
use common\php\JSONException;

class APIJsonValidatorModule extends APIModule {
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
			$args['schema'],
			Constraint::CHECK_MODE_APPLY_DEFAULTS
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
