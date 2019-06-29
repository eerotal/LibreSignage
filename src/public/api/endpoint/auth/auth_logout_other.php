<?php
/*
*  ====>
*
*  Logout all sessions of the user corresponding to the supplied
*  authentication key except the calling session.
*
*  **Request:** POST, application/json
*
*  <====
*/

namespace pub\api\endpoints\auth;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \api\APIEndpoint;

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

		$user->session_n_rm($session->get_id());
		$user->write();

		return [];
	}
);
