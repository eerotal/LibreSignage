<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/defs.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module.php');

class APIRequestValidatorModule extends APIModule {
	/*
	*  Check that the request is correctly formed.
	*/
	public function __construct() {
		parent::__construct();
	}

	public function run(APIEndpoint $endpoint) {
		// Check the request method.
		$method = array_search($endpoint->get_method(), API_METHOD);
		if ($_SERVER['REQUEST_METHOD'] != $method) {
			throw new ArgException(
				"Invalid request method '{$_SERVER['REQUEST_METHOD']}'. ".
				"Expected '{$method}'."
			);
		}

		// Check the request MIME type for POST requests.
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (
				!preg_match(
					API_MIME_REGEX_MAP[$endpoint->get_request_type()],
					$_SERVER['CONTENT_TYPE']
				)
			) {  
				throw new ArgException("Invalid request MIME type.");
			}
		}
	}
}
