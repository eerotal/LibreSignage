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
*    * error = An error code or API_E_OK on success.
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
	function($req, $resp, $params) {
		return [
			'user' => $params['APIAuthModule']['user']->export(FALSE, FALSE),
			'session' => $params['APIAuthModule']['session']->export(FALSE, FALSE)
		];
	}
);
