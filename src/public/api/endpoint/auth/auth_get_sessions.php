<?php
/** \file
* Get the active sessions of the current user as an array.
*
* @method{GET}
* @auth{By token}
* @groups{admin|editor|display}
* @ratelimit_yes
*
* @response_start{application/json}
* @response{array,sessions,An array of session objects.}
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
		$data = ['sessions' => []];
		$user = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

		foreach ($user->get_sessions() as $s) {
			$data['sessions'][] = $s->export(FALSE, FALSE);
		}
		return $data;
	}
);
