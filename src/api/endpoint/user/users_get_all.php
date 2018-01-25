<?php
	/*
	*  API endpoint for getting a list of all the existing
	*  users and the available user data.
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

	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_errors.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');

	header_plaintext();
	session_start();
	auth_init();
	if (!auth_is_authorized('admin', FALSE)) {
		error_and_exit(API_E_NOT_AUTHORIZED);
	}

	$users = auth_get_users();
	$ret_data = array(
		'users' => array(),
		'error' => API_E_OK
	);

	foreach ($users as $u) {
		$ret_data['users'][$u->get_name()] = array(
			'user' => $u->get_name(),
			'groups' => $u->get_groups()
		);
	}

	$ret_str = json_encode($ret_data);
	if ($ret_str === FALSE && json_last_error() != JSON_ERROR_NONE) {
		error_and_exit(API_E_INTERNAL);
	}
	echo $ret_str;
