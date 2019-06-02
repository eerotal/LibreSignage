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
*    * error      = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $resp, $module_data) {
		$ret = ['users' => []];
		$caller = $module_data['APIAuthModule']['user'];

		if (!$caller->is_in_group('admin')) {
			throw new APIException(API_E_NOT_AUTHORIZED, "Not authorized.");
		}

		foreach (user_array() as $u) {
			$ret['users'][$u->get_name()] = [
				'user' => $u->get_name(),
				'groups' => $u->get_groups()
			];
		}
		return $ret;
	}
);
