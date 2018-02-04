<?php
	/*
	*  API endpoint for removing a user.
	*
	*  POST parameters:
	*    * user    = The user to remove.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * error      = An error code or API_E_OK on success. ***
	*
	*    **  (Only exists if the API call was successful.)
	*    *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');

	$USER_REMOVE = new APIEndpoint(
		$method = API_METHOD['POST'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'user' => API_P_STR,
		)
	);
	api_endpoint_init($USER_REMOVE);

	session_start();
	auth_init();
	if (!auth_is_authorized(array('admin'), NULL, FALSE)) {
		api_throw(API_E_NOT_AUTHORIZED);
	}

	$u = _auth_get_user_by_name($USER_REMOVE->get('user'));
	if ($u == NULL) {
		api_throw(API_E_INVALID_REQUEST);
	}

	try {
		$u->remove();
	} catch (Exception $e) {
		api_throw(API_E_INTERNAL, $e);
	}

	$USER_REMOVE->send();
