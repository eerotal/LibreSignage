<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  ====>
*
*  *Get a list of the active sessions for the current user.*
*
*  Return value
*    * sessions = An array of sessions.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$AUTH_GET_SESSIONS = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array(),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($AUTH_GET_SESSIONS);

$resp = array(
	'active' => array()
);
$tok = $AUTH_GET_SESSIONS->get_auth_token();
$sd = $AUTH_GET_SESSIONS->get_caller()->get_session_data();
foreach ($sd as $k => $d) {
	$resp['sessions'][] = array(
		'who' => $d['who'],
		'from' => $d['from'],
		'created' => $d['created'],
		'max_age' => $d['max_age'],
		'current' => password_verify($tok, $d['token_hash'])
	);
}
$AUTH_GET_SESSIONS->resp_set($resp);
$AUTH_GET_SESSIONS->send();
