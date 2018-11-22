<?php
/*
*  ====>
*
*  Create a new user.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * user    = The user to create.
*    * groups  = New groups (Optionally unset or NULL)
*
*  Return value
*    * user
*
*      * name   = The name of the user.
*      * groups = The groups the user is in.
*      * pass   = The generated cleartext password.
*
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

define('DEFAULT_PASSWD_LEN', 10);
define('GROUPS_REGEX', '/[^A-Za-z0-9_]/');
define('USER_REGEX', GROUPS_REGEX);

$USER_CREATE = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY => array(
		'user' => API_P_STR,
		'groups' => API_P_ARR_STR|API_P_OPT|API_P_NULL
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));

if (!$USER_CREATE->get_caller()->is_in_group('admin')) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized."
	);
}

if (user_exists($USER_CREATE->get('user'))) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"User already exists."
	);
}

$user = new User();
$tmp_pass = '';

// Validate user name.
if (preg_match(USER_REGEX, $USER_CREATE->get('user'))) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Invalid chars in group names."
	);
}
try {
	$user->set_name($USER_CREATE->get('user'));
} catch (Exception $e) {
	throw new APIException(
		API_E_LIMITED,
		"Limited.", 0, $e
	);
}

// Validate group names.
if ($USER_CREATE->has('groups', TRUE)) {
	if (count(preg_grep(GROUPS_REGEX,
		$USER_CREATE->get('groups')))) {

		throw new APIException(
			API_E_INVALID_REQUEST,
			"Invalid chars in group names."
		);
	}
	try {
		$user->set_groups($USER_CREATE->get('groups'));
	} catch (Exception $e) {
		throw new APIException(
			API_E_LIMITED,
			"Limited.", 0, $e
		);
	}
}

try {
	$tmp_pass = gen_passwd(DEFAULT_PASSWD_LEN);
} catch (Exception $e) {
	throw new APIException(
		API_E_INTERNAL,
		"Failed to generate password.", 0, $e
	);
}
$user->set_password($tmp_pass);

if ($user->write() === FALSE) {
	throw new APIException(
		API_E_LIMITED,
		"Too many users."
	);
}

$ret = [
	'user' => array_merge(
		$user->export(FALSE, FALSE),
		['pass' => $tmp_pass]
	)
];

$USER_CREATE->resp_set($ret);
$USER_CREATE->send();
