<?php
/*
*  ====>
*
*  *Generate a new one-time use login token.*
*
*  Return value
*    * token = The generated login token.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$AUTH_NEW_LOGIN_TOKEN = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array(),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($AUTH_NEW_LOGIN_TOKEN);

$lt = $AUTH_NEW_LOGIN_TOKEN->get_caller()->login_token_new();
$AUTH_NEW_LOGIN_TOKEN->get_caller()->write();

$AUTH_NEW_LOGIN_TOKEN->resp_set(array(
	'token' => $lt,
	'error' => API_E_OK
));
$AUTH_NEW_LOGIN_TOKEN->send();
