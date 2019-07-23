<?php
/** \file
* Log out all sessions of the current user except the calling one.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor|display}
* @ratelimit_yes
*
* @status_start
* @status{200,On success.}
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

		$user->session_n_rm($session->get_id());
		$user->write();

		return [];
	}
);
