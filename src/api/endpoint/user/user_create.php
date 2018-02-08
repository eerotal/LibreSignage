<?php
	/*
	*  API endpoint for creating a new user.
	*
	*  POST parameters:
	*    * user    = The user to create.
	*    * groups  = New groups (Optionally unset or NULL)
	*
	*  Return value:
	*    A JSON encoded dictionary with the following keys.
	*      * user  **
	*        * name     = The name of the user.
	*        * groups   = The groups the user is in.
	*        * pass     = The generated cleartext password.
	*      * error      = An error code or API_E_OK on success. ***
	*
	*    **  (Only exists if the API call was successful.)
	*    *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

	define('DEFAULT_PASSWD_LEN', 10);
	define('GROUPS_REGEX', '/[^A-Za-z0-9_]/');
	define('USER_REGEX', GROUPS_REGEX);

	$USER_CREATE = new APIEndpoint(
		$method = API_METHOD['POST'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'user' => API_P_STR,
			'groups' => API_P_ARR|API_P_OPT|API_P_NULL
		)
	);
	api_endpoint_init($USER_CREATE);

	session_start();
	auth_init();

	if (!auth_is_authorized(array('admin'), NULL, FALSE)) {
		api_throw(API_E_NOT_AUTHORIZED);
	}

	if (user_exists($USER_CREATE->get('user'))) {
		api_throw(API_E_INVALID_REQUEST);
	}

	$user = new User();
	$tmp_pass = '';

	// Validate user name.
	if (preg_match(USER_REGEX, $USER_CREATE->get('user'))) {
		api_throw(API_E_INVALID_REQUEST,
			new Exception('Invalid chars in group names.')
		);
	}
	$user->set_name($USER_CREATE->get('user'));

	// Validate group names.
	if ($USER_CREATE->has('groups', TRUE)) {
		if (count(preg_grep(GROUPS_REGEX,
			$USER_CREATE->get('groups')))) {

			api_throw(API_E_INVALID_REQUEST,
				new Exception(
					'Invalid chars in '.
					'group names.'
				)
			);
		}
		$user->set_groups($USER_CREATE->get('groups'));
	}

	try {
		$tmp_pass = gen_passwd(DEFAULT_PASSWD_LEN);
	} catch (Exception $e) {
		api_throw(API_E_INTERNAL, $e);
	}
	$user->set_password($tmp_pass);
	$user->set_ready(TRUE);

	try {
		if ($user->write() === FALSE) {
			api_throw(API_E_LIMITED);
		}
	} catch (Exception $e) {
		api_throw(API_E_INTERNAL, $e);
	}

	$ret = array(
		'user' => array(
			'name' => $user->get_name(),
			'groups' => $user->get_groups(),
			'pass' => $tmp_pass
		)
	);

	$USER_CREATE->resp_set($ret);
	$USER_CREATE->send();
