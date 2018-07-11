<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  ====>
*
*  *Get a list of the existing usernames.*
*
*  Return value
*    * users = An array of usernames.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$USERS_GET_ALL = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array(),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($USERS_GET_ALL);

$ret = ['users' => []];
foreach (user_array() as $u) {
	$ret['users'][] = $u->get_name();
}
$USERS_GET_ALL->resp_set($ret);
$USERS_GET_ALL->send();
