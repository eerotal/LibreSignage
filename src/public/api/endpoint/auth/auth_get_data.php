<?php
/** \file
* Get the authentication data for the current session.
*
* @method{GET}
* @auth{By token}
* @groups{admin|editor|display}
* @ratelimit_yes
*
* @response_start{application/json}
* @response{User,user,Current user data.}
* @response{Session,session,Current session data.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status_end
*/

namespace libresignage\api\endpoint\auth;

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
		return [
			'user' => $module_data['APIAuthModule']['user']->export(FALSE, FALSE),
			'session' => $module_data['APIAuthModule']['session']->export(FALSE, FALSE)
		];
	}
);
