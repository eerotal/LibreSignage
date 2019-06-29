<?php
/*
*  ====>
*
*  Get a list of the existing usernames.
*
*  **Request:** GET
*
*  Return value
*    * users = An array of usernames.
*
*  <====
*/

namespace pub\api\endpoints\user;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \api\APIEndpoint;
use \common\php\auth\User;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $resp, $module_data) {
		$ret = ['users' => []];
		foreach (User::all() as $u) { $ret['users'][] = $u->get_name(); }
		return $ret;
	}
);
