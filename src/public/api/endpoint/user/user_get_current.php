<?php
/** \file
* Get information about the calling user.
*
* @method{GET}
* @auth{By token}
* @groups{ALL}
* @ratelimit_yes
*
* @response_start{application/json}
* @response{User,user,The userdata of the calling user.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status_end
*/

namespace libresignage\api\endpoint\user;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $module_data) {
		return ['user' => $module_data['APIAuthModule']['user']->export(FALSE, FALSE)];
	}
);
