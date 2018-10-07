<?php
/*
*  ====>
*
*  Get a list of the active sessions for the current user.
*
*  **Request:** GET
*
*  Return value
*    * sessions = An array of sessions.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$AUTH_GET_SESSIONS = new APIEndpoint(array(
	APIEndpoint::METHOD         => API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::FORMAT_URL     => array(),
	APIEndpoint::REQ_QUOTA      => TRUE,
	APIEndpoint::REQ_AUTH       => TRUE
));
api_endpoint_init($AUTH_GET_SESSIONS);

$resp = [];
$tmp = [];
$current_session = $AUTH_GET_SESSIONS->get_session();
$sessions = $AUTH_GET_SESSIONS->get_caller()->get_sessions();

foreach ($sessions as $k => $s) {
	$tmp = $s->export(FALSE, FALSE);
	$tmp['current'] = ($current_session->get_id() === $s->get_id());
	$resp['sessions'][] = $tmp;
}
$AUTH_GET_SESSIONS->resp_set($resp);
$AUTH_GET_SESSIONS->send();
