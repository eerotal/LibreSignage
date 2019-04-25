<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

class APIAuthModule extends APIModule {
	/*
	*  Check authentication and assign the user and session
	*  data into the supplied endpoint.
	*/
	public function __construct() {
		parent::__construct();
	}

	public function run(APIEndpoint $endpoint) {
		$token = NULL;
		$adata = NULL; // This must be initially NULL. Don't change.

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
