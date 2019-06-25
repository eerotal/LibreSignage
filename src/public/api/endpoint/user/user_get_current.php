<?php
/*
*  ====>
*
*  Get the data of the current user.
*
*  **Request:** GET
*
*  Return value
*    * user
*
*      * user     = The name of the user.
*      * groups   = The groups the user is in.
*
*  <====
*/

namespace pub\api\endpoints\user;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
use \api\APIEndpoint;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function ($req, $resp, $module_data) {
		return ['user' => $module_data['APIAuthModule']['user']->export(FALSE, FALSE)];
	}
);
