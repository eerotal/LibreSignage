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

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\common\php\Config;
use libresignage\api\APIEndpoint;

APIEndpoint::GET(
	[],
	function($req, $module_data) {
		return [
			'ls' => Config::config('LS_VER'),
			'api' => Config::config('API_VER')
		];
	}
);
