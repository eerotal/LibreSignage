<?php
/*
*  ====>
*
*  Get the configured server limits. This endpoint doesn't require
*  or consume the API rate quota.
*
*  **Request:** GET
*
*  Return value
*    * limits     = A dictionary with the limits.
*    * error      = An error code or API_E_OK on success.
*
*  <====
*/

require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

$SERVER_LIMITS = new APIEndpoint([
	APIEndpoint::METHOD 		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_AUTH		=> FALSE
]);

$SERVER_LIMITS->resp_set(array('limits' => LS_LIM));
$SERVER_LIMITS->send();
