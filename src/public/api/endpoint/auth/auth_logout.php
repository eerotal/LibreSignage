<?php
/*
*  ====>
*
*  Logout the current session.
*
*  **Request:** POST, application/json
*
*  Return value
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

APIEndpoint::POST(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		]
	],
	function($req, $resp, $params) {
		$user = $params['APIAuthModule']['user'];
		$session = $params['APIAuthModule']['session'];

		$user->session_rm($session->get_id());
		$user->write();

		return [];
	}
);
