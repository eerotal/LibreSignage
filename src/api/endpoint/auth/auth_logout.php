<?php
/*
*  ====>
*
*  *Logout the current session*
*
*  Return value
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$AUTH_LOGOUT = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array(),
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_API_KEY	=> TRUE
));
api_endpoint_init($AUTH_LOGOUT);

$AUTH_LOGOUT->get_caller()->rm_api_key($AUTH_LOGOUT->get_api_key());

$AUTH_LOGOUT->resp_set(array('error' => API_E_OK));
$AUTH_LOGOUT->send();
