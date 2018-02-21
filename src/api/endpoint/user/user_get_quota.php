<?php
	/*
	*  ====>
	*
	*  *Get a user's quota based on a username.*
	*
	*  GET parameters
	*    * user = The username to query.
	*
	*  Return value
	*    * quota      = A dictionary with the quota data.
	*    * error      = An error code or API_E_OK on success.
	*
	*  <====
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

	$USER_GET_QUOTA = new APIEndpoint(
		$method = API_METHOD['GET'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'user' => API_P_STR|API_P_OPT|API_P_NULL
		)
	);
	session_start();
	api_endpoint_init($USER_GET_QUOTA, auth_session_user());

	$flag_auth = FALSE;
	$user_name = NULL;

	if ($USER_GET_QUOTA->has('user', TRUE)) {
		// Get quota for the requested user.
		$user_name = $USER_GET_QUOTA->get('user');
	} else {
		// Get quota for the logged in user.
		$user_name = auth_session_user()->get_name();
	}

	// Allow admins or the user themself to get the quota.
	$flag_auth = auth_is_authorized(
		$groups = array('admin'),
		$users = array($user_name),
		FALSE,
		FALSE
	);
	if (!$flag_auth) {
		throw new APIException(
			API_E_NOT_AUTHORIZED,
			"Not authorized."
		);
	}

	try {
		$user = new User($user_name);
	} catch (ArgException $e) {
		throw new APIException(
			API_E_INVALID_REQUEST,
			"Failed to load user.", 0, $e
		);
	}
	$user_quota = new UserQuota($user);

	$USER_GET_QUOTA->resp_set(array(
		'quota' => $user_quota->get_quota_data()
	));
	$USER_GET_QUOTA->send();
