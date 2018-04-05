<?php

/*
* ====>
*
*  *Remove an authentication key from the current user.*
*
*  POST parameters
*    * key = The key to remove.
*
*  Return value
*    * error = An error code or API_E_OK on success.
*
* <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

$USER_REMOVE_KEY = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'key' => API_P_STR
	)
));
api_endpoint_init($USER_REMOVE_KEY, auth_session_user());

if (!auth_is_authorized(array('keys'), NULL, FALSE)) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized to manage keys."
	);
}

$user = auth_session_user();
if (!in_array($USER_REMOVE_KEY->get('key'), $user->get_keys())) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"No such key."
	);
}
$user->set_keys(array_values(array_diff(
	$user->get_keys(),
	array($USER_REMOVE_KEY->get('key'))
)));
$user->write();

$USER_REMOVE_KEY->send();
