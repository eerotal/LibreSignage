<?php
/** \file
* Get information about all users.
*
* @method{GET}
* @auth{By token}
* @groups{admin}
* @ratelimit_yes
*
* @response_start{application/json}
* @response{array,users,}
*   @response{object,[username],}
*     @response{string,user,The name of the user.}
*     @response{array,groups,The groups of the user.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{401,If the user is not allowed to get information about all users.}
* @status_end
*/

namespace libresignage\api\endpoint\user;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\auth\User;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $module_data) {
		$ret = ['users' => []];
		$caller = $module_data['APIAuthModule']['user'];

		if (!$caller->is_in_group('admin')) {
			throw new APIException(
				"Not authorized as non-admin user.",
				HTTPStatus::UNAUTHORIZED
			);
		}

		foreach (User::all() as $u) {
			$ret['users'][$u->get_name()] = [
				'user' => $u->get_name(),
				'groups' => $u->get_groups()
			];
		}
		return $ret;
	}
);
