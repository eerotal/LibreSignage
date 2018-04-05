<?php

/*
*  ====>
*
*  *Get the messages corresponding to different API error codes.
*  This endpoint doesn't require or consume the API rate quota.*
*
*  Return value
*    * messages = A dictionary of error messages.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');

$API_ERR_MSGS = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::REQ_QUOTA		=> FALSE
));
api_endpoint_init($API_ERR_MSGS, auth_session_user());

$API_ERR_MSGS->resp_set(array('messages' => API_E_MSG));
$API_ERR_MSGS->send();
