<?php
/** \file
* Renew the current session.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor|display}
* @ratelimit_yes
*
* @response_start{application/json}
* @response{Session,session,The renewed session object.}
* @response_end
*
* @status_start
* @status{200,On success}
* @status_end
*/

namespace libresignage\api\endpoint\auth;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;

APIEndpoint::POST(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $module_data) {
		$user = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

		$session->renew();
		$user->write();

		return ['session' => $session->export(FALSE, FALSE)];
	}
);
