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
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIInterface.php');

APIEndpoint::GET(
	[],
	function($req, $resp, $module_data) {
		return ['limits' => LS_LIMITS];
	}
);
