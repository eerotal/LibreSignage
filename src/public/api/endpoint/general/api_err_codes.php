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

APIEndpoint::GET(
	[],
	function($req, $resp, $params) {
		return ['codes' => API_E];
	}
);
