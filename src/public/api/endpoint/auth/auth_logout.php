<?php
/*
*  ====>
*
*  Logout the current session.
*
*  **Request:** POST, application/json
*
*  <====
*/

namespace pub\api\endpoints\auth;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
use \api\APIEndpoint;

APIEndpoint::POST(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		]
	],
	function($req, $resp, $module_data) {
		$user = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

		$user->session_rm($session->get_id());
		$user->write();

		return [];
	}
);
