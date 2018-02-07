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
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');

	$USER_GET_QUOTA = new APIEndpoint(
		$method = API_METHOD['GET'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'user' => API_P_STR
		)
	);
	api_endpoint_init($USER_GET_QUOTA);

	session_start();
	auth_init();
	if (!auth_is_authorized(array('admin'), NULL, FALSE, FALSE)) {
		api_throw(API_E_NOT_AUTHORIZED);
	}

	$u = _auth_get_user_by_name($USER_GET_QUOTA->get('user'));
	if ($u == NULL) {
		api_throw(API_E_INVALID_REQUEST);
	}
	$u_quota = new UserQuota($u);
	$ret_data = array(
		'quota' => $u_quota->get_data()
	);

	$USER_GET_QUOTA->resp_set($ret_data);
	$USER_GET_QUOTA->send();
