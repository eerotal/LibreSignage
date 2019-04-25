<?php
/*
*  ====>
*
*  Request a session renewal. The session token is preserved but
*  its expiration time is reset.
*
*  **Request:** POST, application/json
*
*  Return value
*    * session = An associative array with the latest session data.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$AUTH_SESSION_RENEW = new APIEndpoint([
	APIEndpoint::METHOD         => API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY    => [],
	APIEndpoint::REQ_QUOTA      => TRUE,
	APIEndpoint::REQ_AUTH       => TRUE
]);

$session = $AUTH_SESSION_RENEW->get_session();
$session->renew();
$AUTH_SESSION_RENEW->get_caller()->write();

// Set the session cookies.
$exp = 0;
if ($session->is_permanent()) {
	$exp = PERMACOOKIE_EXPIRE;
} else {
	$exp = $session->get_created() + $session->get_max_age();
}
setcookie(
	$name = 'session_created',
	$value = $session->get_created(),
	$expire = $exp,
	$path = '/'
);
setcookie(
	$name = 'session_max_age',
	$value = $session->get_max_age(),
	$expire = $exp,
	$path = '/'
);
setcookie(
	$name = 'session_permanent',
	$value = $session->is_permanent() ? '1' : '0',
	$expire = $exp,
	$path = '/'
);

$AUTH_SESSION_RENEW->resp_set(array(
	'session' => $AUTH_SESSION_RENEW->get_session()->export(FALSE, FALSE),
	'error' => API_E_OK
));
$AUTH_SESSION_RENEW->send();
