<?php
/*
*  ====>
*
*  *Logout using the authentication system.*
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

$AUTH_LOGOUT = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(),
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($AUTH_LOGOUT, auth_session_user());

auth_logout();

$AUTH_LOGOUT->resp_set(array('error' => API_E_OK));
$AUTH_LOGOUT->send();
