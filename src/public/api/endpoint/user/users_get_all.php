<?php
/*
*  ====>
*
*  Get a list of all existing users along with the
*  available userdata. Admin privileges are required
*  for this endpoint.
*
*  **Request:** GET
*
*  Return value
*    * users = A dictionary of the users and their data
*      with the usernames as the keys.
*
*      * user     = The name of the user.
*      * groups   = The groups the user is in.
*
*  <====
*/

namespace pub\api\endpoints\user;

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
