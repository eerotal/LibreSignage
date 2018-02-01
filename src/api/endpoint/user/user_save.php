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
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');

	define('GROUP_NAME_COMP_REGEX', '/[^A-Za-z0-9_]/');
	define('USER_NAME_COMP_REGEX', GROUP_NAME_COMP_REGEX);

	$USER_SAVE = new APIEndpoint(
		$method = API_METHOD['POST'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'user' => API_P_STR,
			'pass' => API_P_STR|API_P_OPT|API_P_NULL,
			'groups' => API_P_ARR|API_P_OPT|API_P_NULL
		)
	);
	api_endpoint_init($USER_SAVE);

	session_start();
	auth_init();
	if (!auth_is_authorized('admin', FALSE)) {
		api_throw(API_E_NOT_AUTHORIZED);
	}

	$new_user = FALSE;
	$u = _auth_get_user_by_name($USER_SAVE->get('user'));
	if ($u == NULL) {
		// Create new user.
		$u = new User();
		if (!$USER_SAVE->has('pass')) {
			// New users must have a password.
			api_throw(API_E_INVALID_REQUEST);
		}
		if (count(preg_grep(USER_NAME_COMP_REGEX,
			$USER_SAVE->get('user')))) {

			api_throw(API_E_INVALID_REQUEST,
				new Exception(
					'Invalid chars in username.'
				)
			);
		}
		$u->set_name($USER_SAVE->get('user'));
		$u->set_groups(array());
	}
	if ($USER_SAVE->has('pass')) {
		if ($USER_SAVE->get('pass') != NULL) {
			$u->set_password($USER_SAVE->get('pass'));
		}
	}
	if ($USER_SAVE->has('groups')) {
		if ($USER_SAVE->get('groups') != NULL) {
			if (count(preg_grep(GROUP_NAME_COMP_REGEX,
				$USER_SAVE->get('groups')))) {

				api_throw(API_E_INVALID_REQUEST,
					new Exception(
						'Invalid chars in '.
						'group names.'
					)
				);
			}
			$u->set_groups($USER_SAVE->get('groups'));
		}
	}

	// Make sure new users are considered valid.
	$u->set_ready(TRUE);

	try {
		$u->write();
	} catch (Exception $e) {
		api_throw(API_E_INTERNAL, $e);
	}

	$ret = array(
		'name' => $u->get_name(),
		'groups' => $u->get_groups()
	);

	$USER_SAVE->resp_set($ret);
	$USER_SAVE->send();
