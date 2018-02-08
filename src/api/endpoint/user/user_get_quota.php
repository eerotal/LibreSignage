<?php
	/*
	*  API endpoint for getting a user's quota based on the username.
	*
	*  GET parameters:
	*    * user = The username to query.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * quota      = A dictionary with the quota limits and
	*                     how much quota is used.
	*      * error      = An error code or API_E_OK on success. ***
	*
	*    **  (Only exists if the API call was successful.)
	*    *** (The error codes are listed in api_errors.php.)
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
	api_endpoint_init($USER_GET_QUOTA);

	session_start();
	auth_init();

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
		api_throw(API_E_NOT_AUTHORIZED);
	}

	try {
		$user = new User($user_name);
	} catch (ArgException $e) {
		api_throw(API_E_INVALID_REQUEST, $e);
	}
	$user_quota = new UserQuota($user);

	$USER_GET_QUOTA->resp_set(array(
		'quota' => $user_quota->get_data()
	));
	$USER_GET_QUOTA->send();
