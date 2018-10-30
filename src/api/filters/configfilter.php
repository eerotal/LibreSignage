<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/filters/filter.php');

class APIConfigFilter extends APIFilter {
	/*
	*  Check the API endpoint config for invalid configuration
	*  or dangerous config combinations. This filter also
	*  sends the required Content-Type header.
	*/
	public function __construct() {
		parent::__construct();
	}

	public function filter(APIEndpoint $endpoint) {
		// Send the proper Content-Type header.
		$mime = array_search($endpoint->get_response_type(), API_MIME);
		header("Content-Type: {$mime}");

		/*
		*  Don't allow cookie auth for POST requests.
		*  This prevents CSRF attacks.
		*/
		if (array_search($endpoint->get_method(), API_METHOD) === 'POST') {
			if ($endpoint->allows_cookie_auth()) {
				throw new APIException(
					API_E_INTERNAL,
					"Won't allow cookie auth for POST endpoints."
				);
			}
		}
	}
}
