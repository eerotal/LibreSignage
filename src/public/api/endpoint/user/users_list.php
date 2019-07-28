<?php
/** \file
* Get all usernames as an array.
*
* @method{GET}
* @auth{By token}
* @groups{ALL}
* @ratelimit_yes
*
* @response_start{application/json}
* @response{array,users,An array of usernames.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status_end
*/

namespace libresignage\api\endpoint\user;

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
