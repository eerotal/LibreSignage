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
*    * error      = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function ($req, $resp, $params) {
		return ['user' => $params['APIAuthModule']['user']->export(FALSE, FALSE)];
	}
);
