<?php

namespace libresignage\api\modules;

use libresignage\api\APIEndpoint;
use libresignage\api\APIModule;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use libresignage\common\php\JSONUtils;
use libresignage\common\php\exceptions\JSONException;
use libresignage\common\php\Util;
use libresignage\common\php\Log;

/**
* API module for validating and decoding a request with a JSON body.
*/
class APIJSONValidatorModule extends APIModule {
	/**
	* Decode and validate the request body JSON.
	*
	* @see APIModule for argument and return value descriptions.
	*
	* @throws APIException if the request Content-Type is not application/json.
	* @throws APIException if decoding the request JSON fails.
	* @throws APIException if the request JSON doesn't match the
	*                      provided JSON schema.
	*/
	public function run(APIEndpoint $e, array $args) {
		$data = NULL;
		$this->check_args(['schema'], $args);

		$content_type = $e->get_request()->headers->get('Content-Type');
		if ($content_type !== 'application/json') {
			throw new APIException(
				"Wrong Content-Type: $content_type. Expected application/json.",
				HTTPStatus::BAD_REQUEST
			);
		}
		return $this->validate($e->get_request()->getContent(), $args['schema']);
	}

	/**
	* Decode a JSON string and validate the resulting object
	* against a JSON schema.
	*
	* @param string $json         The JSON input string.
	* @param object|array $schema The JSON schema.
	*
	* @return object The decoded and validated JSON.
	*/
	public function validate(string $json, $schema) {
		if ($json === '') {
			$data = (object) [];
		} else {
			try {
				$data = JSONUtils::decode($json);
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
			Util::assoc_array_to_object($schema),
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
