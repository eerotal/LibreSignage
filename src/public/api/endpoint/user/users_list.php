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

use libresignage\api\APIEndpoint;
use libresignage\common\php\auth\User;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $module_data) {
		return ['users' => User::names()];
	}
);
