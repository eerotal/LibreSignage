<?php
/*
*  ====>
*
*  Get a user's data based on a username. This endpoint
*  doesn't return any secret information like passwords.
*
*  **Request:** GET
*
*  Parameters
*    * user = The username to query.
*
*  Return value
*    * user
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
		'APIRateLimitModule' => [],
		'APIQueryValidatorModule' => [
			'schema' => [
				'type' => 'object',
				'properties' => [
					'user' => [
						'type' => 'string'
					]
				],
				'required' => ['user']
			]
		]
	],
	function($req, $module_data) {
		$caller = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIQueryValidatorModule'];
		$user = NULL;

		if (!$caller->is_in_group('admin')) {
			throw new APIException(
				'Not authorized as non-admin.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$user = new User();
		$user->load($params->user);

		return [
			'user' => [
				'user' => $user->get_name(),
				'groups' => $user->get_groups()
			]
		];
	}
);
