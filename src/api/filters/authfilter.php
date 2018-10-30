<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/filters/filter.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

class APIAuthFilter extends APIFilter {
	/*
	*  Check authentication and assign the user and session
	*  data into $endpoint.
	*/
	public function __construct() {
		parent::__construct();
	}

	public function filter(APIEndpoint $endpoint) {
		$token = NULL;
		$adata = NULL; // Don't change.

		if (!$endpoint->requires_auth()) { return; }

		if ($endpoint->has_header(AUTH_TOKEN_HEADER)) {
			$token = $endpoint->get_header(AUTH_TOKEN_HEADER);
			$adata = auth_token_verify($token);
		} else if ($endpoint->allows_cookie_auth()) {
			$adata = auth_cookie_verify();
		}

		if ($adata === NULL) {
			throw new APIException(
				API_E_NOT_AUTHORIZED,
				'Not authenticated.'
			);
		}

		$endpoint->set_caller($adata['user']);
		$endpoint->set_session($adata['session']);
	}
}
