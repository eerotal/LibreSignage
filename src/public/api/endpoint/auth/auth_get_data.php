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

namespace libresignage\api\endpoint\auth;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $module_data) {
		return [
			'user' => $module_data['APIAuthModule']['user']->export(FALSE, FALSE),
			'session' => $module_data['APIAuthModule']['session']->export(FALSE, FALSE)
		];
	}
);
