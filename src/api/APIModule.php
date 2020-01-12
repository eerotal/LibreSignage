<?php

namespace libresignage\api;

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;

/**
* Base class for API modules.
*/
abstract class APIModule {
	/**
	* Entry point function for API modules. Override this to implement
	* the module.
	*
	* @param APIEndpoint $e The APIEndpoint object the module is run for.
	* @param array $args An array that contains user defined arguments
	*                    for the module.
	* @return mixed      The return data from them module that's accessible
	*                    to API endpoints.
	*/
	abstract function run(APIEndpoint $e, array $args);

	/**
	* Check that all the required arguments in $req are defined in $args.
	*
	* @throws APIException If all required arguments are not defined in $args.
	*/
	final public function check_args(array $req, array $args) {
		$diff = \array_diff($req, \array_keys($args));
		if (count($diff) === 0) { return; }

		throw new APIException(
			HTTPStatus::INTERNAL_SERVER_ERROR,
			"Missing arguments ".implode(', ', $diff).
			" for API module '".get_class($this)."'."
		);
	}
}
