<?php
/*
*  ====>
*
*  *Request a session renewal. The previous authentication token
*  is automatically expired when this endpoint is called.*
*
*  Return value
*    * auth_token = A newly generated authentication token.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$AUTH_SESSION_RENEW = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array(),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($AUTH_SESSION_RENEW);

// Renew the current session.
$session = $AUTH_SESSION_RENEW->get_caller()->session_renew(
	$AUTH_SESSION_RENEW->get_auth_token()
);
$AUTH_SESSION_RENEW->get_caller()->write();

$AUTH_SESSION_RENEW->resp_set(array(
	'session' => $session,
	'error' => API_E_OK
));
$AUTH_SESSION_RENEW->send();
