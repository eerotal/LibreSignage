<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

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

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$SERVER_LIMITS = new APIEndpoint(array(
	APIEndpoint::METHOD 		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_AUTH		=> FALSE
));
api_endpoint_init($SERVER_LIMITS);

$SERVER_LIMITS->resp_set(array('limits' => LS_LIM));
$SERVER_LIMITS->send();
