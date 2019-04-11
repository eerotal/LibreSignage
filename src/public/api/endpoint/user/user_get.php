<?php
/*
*  ====>
*
*  Get a user's data based on a username. This endpoint
*  doesn't return any secret information like passwords.
*
*  **Request:** GET
*
*  Parameters
*    * user = The username to query.
*
*  Return value
*    * user
*
*      * user     = The name of the user.
*      * groups   = The groups the user is in.
*
*    * error      = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$USER_GET = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::FORMAT_URL => array(
		'user' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));

if (!$USER_GET->get_caller()->is_in_group('admin')) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized."
	);
}

try {
	$u = new User($USER_GET->get('user'));
} catch (ArgException $e) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Failed to load user.", 0, $e
	);
}

$ret_data = [
	'user' => [
		'user' => $u->get_name(),
		'groups' => $u->get_groups()
	]
];

$USER_GET->resp_set($ret_data);
$USER_GET->send();
