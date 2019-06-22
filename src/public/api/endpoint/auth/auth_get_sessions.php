<?php
/*
*  ====>
*
*  Get a list of the active sessions for the current user.
*
*  **Request:** GET
*
*  Return value
*    * sessions = An array of sessions.
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
	function($req, $resp, $module_data) {
		$data = [];
		$tmp = [];
		$user = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

		foreach ($user->get_sessions() as $k => $s) {
			$tmp = $s->export(FALSE, FALSE);
			$tmp['current'] = ($session->get_id() === $s->get_id());
			$data['sessions'][] = $tmp;
		}
		return $data;
	}
);
