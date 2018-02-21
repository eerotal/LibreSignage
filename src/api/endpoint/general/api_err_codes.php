<?php

/*
*  ====>
*
*  *Get the defined API error codes.*
*
*  Return value
*    * codes = A dictionary of error codes.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');

$API_ERR_CODES = new APIEndpoint(
	$method = API_METHOD['GET'],
	$response_type = API_RESPONSE['JSON'],
	$format = NULL
);
session_start();
auth_init();
api_endpoint_init($API_ERR_CODES, auth_session_user());

$API_ERR_CODES->resp_set(array('codes' => API_E));
$API_ERR_CODES->send();
