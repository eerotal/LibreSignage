<?php

/*
* ====>
*
*  *Get the authentication keys of the current user.*
*
*  Return value
*    * keys = An array of the authentication keys.
*    * error = An error code or API_E_OK on success.
*
* <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

$USER_GET_KEYS = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array()
));
api_endpoint_init($USER_GET_KEYS, auth_session_user());

if (!auth_is_authorized(NULL, NULL, FALSE)) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized."
	);
}

$USER_GET_KEYS->resp_set(array(
	'keys' => auth_session_user()->get_keys()
));
$USER_GET_KEYS->send();
