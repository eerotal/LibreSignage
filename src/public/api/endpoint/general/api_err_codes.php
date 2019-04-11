<?php

/*
*  ====>
*
*  Get the defined API error codes. This endpoint doesn't require
*  or consume the API rate quota.
*
*  **Request:** GET
*
*  Return value
*    * codes = A dictionary of error codes.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

$API_ERR_CODES = new APIEndpoint(array(
	APIEndpoint::METHOD         => API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::REQ_QUOTA      => FALSE,
	APIEndpoint::REQ_AUTH       => FALSE
));

$API_ERR_CODES->resp_set(array('codes' => API_E));
$API_ERR_CODES->send();
