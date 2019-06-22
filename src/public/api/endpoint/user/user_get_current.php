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

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIInterface.php');

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
