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

namespace libresignage\api\endpoints\user;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\auth\User;
use libresignage\common\php\exceptions\IntException;
use libresignage\common\php\exceptions\ArgException;

APIEndpoint::POST(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => [],
		'APIJSONValidatorModule' => [
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
		$params = $module_data['APIJSONValidatorModule'];

		if (!$caller->is_in_group('admin')) {
			throw new APIException(
				'Not authorized as a non-admin user.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$u = new User();

		try {
			$u->load($params->user);
		} catch (ArgException $e) {
			throw new APIException(
				"Failed to load user '{$params->user}'.",
				HTTPStatus::BAD_REQUEST,
				$e
			);
		}

		try {
			$u->remove();
		} catch (IntException $e) {
			throw new APIException(
				"Failed to remove user '{$params->user}'.",
				HTTPStatus::INTERNAL_SERVER_ERROR,
				$e
			);
		}

		return [];
	}
);
