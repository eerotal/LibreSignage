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
*  Return value
*    * error      = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

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
			throw new APIException(API_E_NOT_AUTHORIZED, "Not authorized.");
		}

		try {
			$u = new User($params->user);
		} catch (ArgException $e) {
			throw new APIException(
				API_E_INVALID_REQUEST,
				"Failed to load user.", 0, $e
			);
		}

		try {
			$u->remove();
		} catch (Exception $e) {
			throw new APIException(
				API_E_INTERNAL,
				"Failed to remove user.", 0, $e
			);
		}

		return [];
	}
);
