<?php
	/*
	*  API endpoint for saving a user's user data.
	*
	*  POST parameters:
	*    * user    = The user to modify or create.
	*    * pass    = A new password (Optional for existing users)
	*    * groups  = New groups (Optional)
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * users      = A dictionary of the users and their data
	*                     with the usernames as the keys.
	*        * user     = The name of the user.
	*        * groups   = The groups the user is in.
	*      * error      = An error code or API_E_OK on success. ***
	*
	*    **  (Only exists if the API call was successful.)
	*    *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');

	$USER_SAVE = new APIEndpoint(
		$method = API_METHOD['POST'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'user' => API_P_STR,
			'pass' => API_P_STR|API_P_OPT,
			'groups' => API_P_ARR|API_P_OPT
		)
	);
	api_endpoint_init($USER_SAVE);

	session_start();
	auth_init();
	if (!auth_is_authorized('admin', FALSE)) {
		error_and_exit(API_E_NOT_AUTHORIZED);
	}

	$new_user = FALSE;
	$u = _auth_get_user_by_name($USER_SAVE->get('user'));
	if ($u == NULL) {
		// Create new user.
		$u = new User();
		if (!$USER_SAVE->has('pass')) {
			// New users must have a password.
			error_and_exit(API_E_INVALID_REQUEST);
		}
		$u->set_name($USER_SAVE->get('user'));
		$u->set_groups(array());
	}
	if ($USER_SAVE->has('pass')) {
		$u->set_password($USER_SAVE->get('pass'));
	}
	if ($USER_SAVE->has('groups')) {
		$u->set_groups($USER_SAVE->get('groups'));
	}

	// Make sure new users are considered valid.
	$u->set_ready(TRUE);

	try {
		$u->write();
	} catch (Exception $e) {
		error_and_exit(API_E_INTERNAL);
	}

	$ret = array(
		'name' => $u->get_name(),
		'groups' => $u->get_groups(),
		'error' => API_E_OK
	);

	$ret_str = json_encode($ret);
	if ($ret_str === FALSE && json_last_error() != JSON_ERROR_NONE) {
		error_and_exit(API_E_INTERNAL);
	}
	echo $ret_str;

