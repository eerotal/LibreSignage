<?php
/*
*  ====>
*
*  *Get the configured server limits. This endpoint doesn't require
*  or consume the API rate quota.*
*
*  Return value
*    * limits     = A dictionary with the limits.
*    * error      = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

$SERVER_LIMITS = new APIEndpoint(array(
	APIEndpoint::METHOD 		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_API_KEY	=> FALSE
));
api_endpoint_init($SERVER_LIMITS);

$SERVER_LIMITS->resp_set(array('limits' => LS_LIM));
$SERVER_LIMITS->send();
