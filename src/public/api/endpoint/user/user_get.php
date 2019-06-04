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
	function($req, $resp, $module_data) {
		$caller = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIQueryValidatorModule'];
		$user = NULL;

		if (!$caller->is_in_group('admin')) {
			throw new APIException(API_E_NOT_AUTHORIZED, "Not authorized.");
		}
		$user = new User($params->user);

		return [
			'user' => [
				'user' => $user->get_name(),
				'groups' => $user->get_groups()
			]
		];
	}
);
