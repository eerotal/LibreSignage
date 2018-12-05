<?php
/*
*  ====>
*
*  Logout the current session.
*
*  **Request:** POST, application/json
*
*  Return value
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$AUTH_LOGOUT = new APIEndpoint([
	APIEndpoint::METHOD	        => API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY    => array(),
	APIEndpoint::REQ_QUOTA      => FALSE,
	APIEndpoint::REQ_AUTH       => TRUE
]);

$AUTH_LOGOUT->get_caller()->session_rm(
	$AUTH_LOGOUT->get_session()->get_id()
);
$AUTH_LOGOUT->get_caller()->write();

$AUTH_LOGOUT->resp_set(['error' => API_E_OK]);
$AUTH_LOGOUT->send();
