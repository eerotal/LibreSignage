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

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

$AUTH_SESSION_RENEW = new APIEndpoint([
	APIEndpoint::METHOD         => API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY    => [],
	APIEndpoint::REQ_QUOTA      => TRUE,
	APIEndpoint::REQ_AUTH       => TRUE
]);

$AUTH_SESSION_RENEW->get_session()->renew();
$AUTH_SESSION_RENEW->get_caller()->write();

$AUTH_SESSION_RENEW->resp_set([
	'session' => $AUTH_SESSION_RENEW->get_session()->export(FALSE, FALSE),
	'error' => API_E_OK
]);
$AUTH_SESSION_RENEW->send();
