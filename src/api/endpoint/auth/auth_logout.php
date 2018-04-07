<?php
/*
*  ====>
*
*  *Logout the current session*
*
*  POST parameters
*    * PARAM_API_KEY
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
	APIEndpoint::FORMAT		=> array(),
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_API_KEY	=> TRUE
));
api_endpoint_init($AUTH_LOGOUT);

auth_logout();

$AUTH_LOGOUT->resp_set(array('error' => API_E_OK));
$AUTH_LOGOUT->send();
