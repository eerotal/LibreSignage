<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

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

/*
*  Set the session cookies. Note that the server setting these
*  cookies is merely a convenience feature for web browser clients
*  that need to access the LibreSignage web interface too. Other
*  clients can ignore these cookies if so desired.
*/
$exp = 0;
if ($session['permanent']) {
	$exp = PERMACOOKIE_EXPIRE;
} else {
	$exp = $session['created'] + $session['max_age'];
}
setcookie(
	$name = 'session_token',
	$value = $session['token'],
	$expire = $exp,
	$path = '/'
);
setcookie(
	$name = 'session_created',
	$value = $session['created'],
	$expire = $exp,
	$path = '/'
);
setcookie(
	$name = 'session_max_age',
	$value = $session['max_age'],
	$expire = $exp,
	$path = '/'
);
setcookie(
	$name = 'session_permanent',
	$value = $session['permanent'] ? '1' : '0',
	$expire = $exp,
	$path = '/'
);

$AUTH_SESSION_RENEW->resp_set(array(
	'session' => $session,
	'error' => API_E_OK
));
$AUTH_SESSION_RENEW->send();
