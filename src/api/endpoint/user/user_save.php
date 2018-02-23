<?php
/*
*  ====>
*
*  *Save a user's data.*
*
*  Access is granted in any of the following cases.
*
*    1. The logged in user is in the group 'admin' and
*       they are not trying to set a new password. This
*       prevents the admin taking over an account.
*    2. The logged in user is the user to be modified and
*       they are not trying to set user groups. This prevents
*       privilege escalation.
*
*  POST parameters
*    * user    = The user to modify.
*    * pass    = New password (Optionally unset or NULL)
*    * groups  = New groups (Optionally unset or NULL)
*
*  Return value
*    * user
*
*      * name     = The name of the user.
*      * groups   = The groups the user is in.
*
*    * error      = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

define('GROUP_NAME_COMP_REGEX', '/[^A-Za-z0-9_]/');
define('USER_NAME_COMP_REGEX', GROUP_NAME_COMP_REGEX);

$USER_SAVE = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'user' => API_P_STR,
		'pass' => API_P_STR|API_P_OPT|API_P_NULL,
		'groups' => API_P_ARR|API_P_OPT|API_P_NULL
	)
));
session_start();
api_endpoint_init($USER_SAVE, auth_session_user());

// Is authorized as an admin?
$auth_admin = auth_is_authorized(
	$groups = array('admin'),
	$users = NULL,
	FALSE
);

// Is authorized as the user to be modified?
$auth_usr = auth_is_authorized(
	$groups = NULL,
	$users = array($USER_SAVE->get('user')),
	FALSE
);

// Check for authorization.
if (!$auth_admin && !$auth_usr) {
	// Not logged in.
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not logged in."
	);
}

if ($USER_SAVE->has('pass', TRUE) && !$auth_usr) {
	// Case 1. check.
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Admins can't change password of other users."
	);
}

if ($USER_SAVE->has('groups', TRUE) && !$auth_admin) {
	// Case 2. check.
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Non-admin users can't set groups."
	);
}

try {
	$u = new User($USER_SAVE->get('user'));
} catch (ArgException $e) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Failed to load user.", 0, $e
	);
}

if ($USER_SAVE->has('pass', TRUE)) {
	$u->set_password($USER_SAVE->get('pass'));
}
if ($USER_SAVE->has('groups', TRUE)) {
	if (count(preg_grep(GROUP_NAME_COMP_REGEX,
			$USER_SAVE->get('groups')))) {
		throw new APIException(
			API_E_INVALID_REQUEST,
			"Invalid chars in groups names."
		);
	}
	try {
		$u->set_groups($USER_SAVE->get('groups'));
	} catch (Exception $e) {
		throw new APIException(
			API_E_LIMITED,
			"Failed to set user groups.", 0, $e
		);
	}
}

if ($u->write() === FALSE) {
	throw new APIException(
		API_E_LIMITED,
		"Failed to write userdata."
	);
}

$ret = array(
	'user' => array(
		'name' => $u->get_name(),
		'groups' => $u->get_groups()
	)
);

$USER_SAVE->resp_set($ret);
$USER_SAVE->send();
