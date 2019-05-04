<?php
/*
*  ====>
*
*  Get a list of the existing usernames.
*
*  **Request:** GET
*
*  Return value
*    * users = An array of usernames.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

$USERS_GET_ALL = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::FORMAT_URL		=> array(),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));

$ret = ['users' => []];
foreach (user_array() as $u) {
	$ret['users'][] = $u->get_name();
}
$USERS_GET_ALL->resp_set($ret);
$USERS_GET_ALL->send();
