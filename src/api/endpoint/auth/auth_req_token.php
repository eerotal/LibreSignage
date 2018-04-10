<?php
/*
*  ====>
*
*  *Request a new authentication token for a user. The token
*  used when this endpoint was called is automatically expired
*  when this call finishes.*
*
*  Return value
*    * auth_token = A newly generated authentication token.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$AUTH_REQ_TOKEN = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array(),
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($AUTH_REQ_TOKEN);

// Generate the new token.
$auth_token = $AUTH_REQ_TOKEN->get_caller()->gen_auth_token();

// Expire the old token.
$AUTH_REQ_TOKEN->get_caller()->rm_auth_token(
	$AUTH_REQ_TOKEN->get_auth_token()
);
$AUTH_REQ_TOKEN->get_caller()->write();

$AUTH_REQ_TOKEN->resp_set(array(
	'auth_token' => $auth_token,
	'error' => API_E_OK
));
$AUTH_REQ_TOKEN->send();
