<?php

/*
* ====>
*
*  *Generate a new authentication key for the current user.*
*
*  Return value
*    * key = The generated authentication key.
*    * error = An error code or API_E_OK on success.
*
* <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

$USER_GENERATE_KEY = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array()
));
api_endpoint_init($USER_GENERATE_KEY, auth_session_user());

const DEFAULT_KEY_LEN = 15;

if (!auth_is_authorized(array('keys'), NULL, FALSE)) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized to manage keys."
	);
}

$new_key = bin2hex(random_bytes(DEFAULT_KEY_LEN));

$user = auth_session_user();
$keys = $user->get_keys();
array_push($keys, $new_key);
$user->set_keys($keys);
$user->write();

$USER_GENERATE_KEY->resp_set(array(
	'key' => $new_key
));
$USER_GENERATE_KEY->send();
