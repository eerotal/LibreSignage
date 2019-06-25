<?php
/*
*  ====>
*
*  Get LibreSignage version information.
*
*  **Request:** GET
*
*  Return value
*    * main    = The LibreSignage version string.
*    * api     = The API version number.
*
*  <====
*/

namespace pub\api\endpoints\general;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
use \api\APIEndpoint;

APIEndpoint::GET(
	[],
	function($req, $resp, $module_data) {
		return [
			'ls' => LS_VER,
			'api' => API_VER
		];
	}
);
