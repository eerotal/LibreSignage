<?php
/*
*  ====>
*
*  Request a session renewal. The session token is preserved but
*  its expiration time is reset.
*
*  **Request:** POST, application/json
*
*  Return value
*    * session = An associative array with the latest session data.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIInterface.php');

APIEndpoint::POST(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $resp, $module_data) {
		$user = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

		$session->renew();
		$user->write();

		return ['session' => $session->export(FALSE, FALSE)];
	}
);
