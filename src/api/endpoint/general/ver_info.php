<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  ====>
*
*  *Get LibreSignage version information.*
*
*  Return value
*    * main    = The LibreSignage version string.
*    * api     = The API version number.
*    * error   = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$VER_INFO = new APIEndpoint([
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_AUTH		=> FALSE
]);
api_endpoint_init($VER_INFO);

$VER_INFO->resp_set([
	'ls' => LS_VER,
	'api' => API_VER
]);
$VER_INFO->send();
