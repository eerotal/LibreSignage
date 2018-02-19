<?php
	/*
	*  ====>
	*
	*  Get the data of the current user.
	*
	*  Return value:
	*    user
	*      user     = The name of the user.
	*      groups   = The groups the user is in.
	*    error      = An error code or API_E_OK on success.
	*
	*  <====
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

	$USER_GET = new APIEndpoint(
		$method = API_METHOD['GET'],
		$response_type = API_RESPONSE['JSON'],
		$format = NULL
	);
	api_endpoint_init($USER_GET);

	session_start();
	auth_init();
	if (!auth_is_authorized(NULL, NULL, FALSE)) {
		api_throw(API_E_NOT_AUTHORIZED);
	}

	$u = auth_session_user();
	$ret_data = array(
		'user' => array(
			'user' => $u->get_name(),
			'groups' => $u->get_groups()
		)
	);
	$USER_GET->resp_set($ret_data);
	$USER_GET->send();
