<?php
/*
*  ====>
*
*  *Login using the authentication system.*
*
*  POST parameters
*    * username    = Username
*    * password    = Password
*
*  Return value
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

$AUTH_LOGIN = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'username' => API_P_STR,
		'password' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_AUTH		=> FALSE
));
api_endpoint_init($AUTH_LOGIN, NULL);

$ret = auth_login(
	$AUTH_LOGIN->get('username'),
	$AUTH_LOGIN->get('password')
);

if ($ret) {
	$AUTH_LOGIN->resp_set(array(
		'error' => API_E_OK
	));
} else {
	$AUTH_LOGIN->resp_set(array(
		'error' => API_E_INCORRECT_CREDS
	));
}
$AUTH_LOGIN->send();
