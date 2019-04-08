<?php
/*
*  ====>
*
*  Request a session renewal. The previous authentication token
*  is automatically expired when this endpoint is called. If this
*  endpoint is called with permanent or orphan sessions, the session
*  is not renewed and this endpoint returns 'renewed' => FALSE.
*
*  **Request:** POST, application/json
*
*  Return value
*    * renewed = Whether the session was renewed or not.
*    * session = An associative array with the latest session data.
*                This array also contains the session token under 'token'
*                when the session is renewed.
*    * error   = An error code or API_E_OK on success.
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

$current_session = $AUTH_SESSION_RENEW->get_session();
$current_user = $AUTH_SESSION_RENEW->get_caller();

// Don't renew permanent or orphan sessions.
if ($current_session->is_permanent() || $current_session->is_orphan()) {
	$AUTH_SESSION_RENEW->resp_set([
		'renewed' => FALSE,
		'session' => $current_session->export(FALSE, FALSE),
		'error' => API_E_OK
	]);
	$AUTH_SESSION_RENEW->send();
}

$new_session = $current_user->session_renew_by_id($current_session->get_id());
$current_user->write();

// Set the new session cookies.
$exp = $new_session->get_created() + $new_session->get_max_age();
setcookie(
	$name = 'session_token',
	$value = $new_session->get_token(),
	$expire = $exp,
	$path = '/'
);
setcookie(
	$name = 'session_created',
	$value = $new_session->get_created(),
	$expire = $exp,
	$path = '/'
);
setcookie(
	$name = 'session_max_age',
	$value = $new_session->get_max_age(),
	$expire = $exp,
	$path = '/'
);
setcookie(
	$name = 'session_permanent',
	$value = $new_session->is_permanent() ? '1' : '0',
	$expire = $exp,
	$path = '/'
);

$AUTH_SESSION_RENEW->resp_set([
	'renewed' => TRUE,
	'session' => array_merge(
		$new_session->export(FALSE, FALSE),
		['token' => $new_session->get_token()]
	),
	'error' => API_E_OK
]);
$AUTH_SESSION_RENEW->send();
