<?php
	/*
	*  ====>
	*
	*  *Get the configured server limits.*
	*
	*  Return value
	*    * limits     = A dictionary with the limits.
	*    * error      = An error code or API_E_OK on success.
	*
	*  <====
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

	$SERVER_LIMITS = new APIEndpoint(
		$method = API_METHOD['GET'],
		$response_type = API_RESPONSE['JSON'],
		$format = NULL
	);
	api_endpoint_init($SERVER_LIMITS);

	session_start();
	auth_init();
	if (!auth_is_authorized(NULL, NULL, FALSE)) {
		api_throw(API_E_NOT_AUTHORIZED);
	}

	$SERVER_LIMITS->resp_set(array('limits' => LS_LIM));
	$SERVER_LIMITS->send();
