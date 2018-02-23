<?php
/*
*  ====>
*
*  *Remove a user based on a username.*
*
*  POST parameters
*    * user    = The user to remove.
*
*  Return value
*    * error      = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

$USER_REMOVE = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'user' => API_P_STR,
	)
));
session_start();
api_endpoint_init($USER_REMOVE, auth_session_user());

if (!auth_is_authorized(array('admin'), NULL, FALSE)) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized."
	);
}

try {
	$u = new User($USER_REMOVE->get('user'));
} catch (ArgException $e) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Failed to load user.", 0, $e
	);
}

try {
	$u->remove();
} catch (Exception $e) {
	throw new APIException(
		API_E_INTERNAL,
		"Failed to remove.", 0, $e
	);
}

$USER_REMOVE->send();
