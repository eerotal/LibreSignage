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

namespace pub\api\endpoints\general;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \api\APIEndpoint;

APIEndpoint::GET(
	[],
	function($req, $module_data) {
		return ['limits' => LS_LIMITS];
	}
);
