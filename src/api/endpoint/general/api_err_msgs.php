<?php

/*
*  ====>
*
*  *Get the messages corresponding to different API error codes.*
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

$API_ERR_MSGS = new APIEndpoint(
	$method = API_METHOD['GET'],
	$response_type = API_RESPONSE['JSON'],
	$format = NULL
);
session_start();
api_endpoint_init($API_ERR_MSGS, auth_session_user());

$API_ERR_MSGS->resp_set(array('messages' => API_E_MSG));
$API_ERR_MSGS->send();
