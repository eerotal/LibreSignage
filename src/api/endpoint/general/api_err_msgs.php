<?php
/*
*  ====>
*
*  Get the messages corresponding to different API error codes.
*  This endpoint doesn't require or consume the API rate quota.
*
*  **Request:** GET
*
*  Return value
*    * messages = A dictionary of error messages.
*    * error    = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$API_ERR_MSGS = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_AUTH		=> FALSE
));

$API_ERR_MSGS->resp_set(array('messages' => API_E_MSG));
$API_ERR_MSGS->send();
