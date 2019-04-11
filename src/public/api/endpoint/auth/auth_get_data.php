<?php
/*
*  ====>
*
*  Get the current authentication data.
*
*  **Request:** GET
*
*  Return value
*    * user = Current use data.
*    * session = Current session data.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

$AUTH_GET_DATA = new APIEndpoint(array(
	APIEndpoint::METHOD         => API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::FORMAT_URL     => array(),
	APIEndpoint::REQ_QUOTA      => TRUE,
	APIEndpoint::REQ_AUTH       => TRUE
));


$AUTH_GET_DATA->resp_set([
	'user' => $AUTH_GET_DATA->get_caller()->export(FALSE, FALSE),
	'session' => $AUTH_GET_DATA->get_session()->export(FALSE, FALSE)
]);
$AUTH_GET_DATA->send();
