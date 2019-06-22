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

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIInterface.php');

APIEndpoint::GET(
	[],
	function($req, $resp, $module_data) {
		return [
			'ls' => LS_VER,
			'api' => API_VER
		];
	}
);
