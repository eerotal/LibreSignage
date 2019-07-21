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
*    * session = The renewed session data.
*
*  <====
*/

namespace pub\api\endpoints\auth;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;

APIEndpoint::POST(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $module_data) {
		$user = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

		$session->renew();
		$user->write();

		return ['session' => $session->export(FALSE, FALSE)];
	}
);
