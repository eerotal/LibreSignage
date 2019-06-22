<?php
/*
*  ====>
*
*  Remove a user based on a username.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * user    = The user to remove.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIInterface.php');

APIEndpoint::POST(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => [],
		'APIJsonValidatorModule' => [
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
		$params = $module_data['APIJsonValidatorModule'];

		if (!$caller->is_in_group('admin')) {
			throw new APIException(
				'Non-admin user not authorized.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		try {
			$u = new User($params->user);
		} catch (ArgException $e) {
			throw new APIException(
				"Failed to load user '{$params->user}'.",
				HTTPStatus::BAD_REQUEST,
				$e
			);
		}

		try {
			$u->remove();
		} catch (Exception $e) {
			throw new APIException(
				"Failed to remove user '{$params->user}'.",
				HTTPStatus::INTERNAL_SERVER_ERROR,
				$e
			);
		}

		return [];
	}
);
