<?php

/*
*  ====>
*
*  Get the current authentication data.
*
*  **Request:** GET
*
*  Return value
*    * user = Current use data.
*    * session = Current session data.
*
*  <====
*/

namespace pub\api\endpoints\auth;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \api\APIEndpoint;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $resp, $module_data) {
		return [
			'user' => $module_data['APIAuthModule']['user']->export(FALSE, FALSE),
			'session' => $module_data['APIAuthModule']['session']->export(FALSE, FALSE)
		];
	}
);
