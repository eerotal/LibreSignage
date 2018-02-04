<?php
	/*
	*  API endpoint for getting a user's data based on the username.
	*
	*  GET parameters:
	*    * user = The username to query.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * users      = A dictionary of the users and their data
	*                    with the usernames as the keys.
	*        * user     = The name of the user.
	*        * groups   = The groups the user is in.
	*      * error      = An error code or API_E_OK on success. ***
	*
	*    **  (Only exists if the API call was successful.)
	*    *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');

	$USER_GET = new APIEndpoint(
		$method = API_METHOD['GET'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'user' => API_P_STR
		)
	);
	api_endpoint_init($USER_GET);

	session_start();
	auth_init();
	if (!auth_is_authorized(array('admin'), NULL, FALSE)) {
		api_throw(API_E_NOT_AUTHORIZED);
	}

	$u = _auth_get_user_by_name($USER_GET->get('user'));
	if ($u == NULL) {
		api_throw(API_E_INVALID_REQUEST);
	}
	$ret_data = array(
		'user' => array(
			'user' => $u->get_name(),
			'groups' => $u->get_groups()
		)
	);

	$USER_GET->resp_set($ret_data);
	$USER_GET->send();
