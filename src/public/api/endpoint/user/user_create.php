<?php
/*
*  ====>
*
*  Create a new user.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * user    = The name of the user to create.
*    * groups  = New groups. If NULL or undefined no groups are set.
*
*  Return value
*    * user = The exported userdata.
*    * pass = The generated cleartext password.
*
*  <====
*/

namespace pub\api\endpoints\user;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\common\php\Config;
use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\auth\User;
use libresignage\common\php\Util;
use libresignage\common\php\exceptions\ArgException;
use libresignage\common\php\exceptions\LimitException;

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
					],
					'groups' => [
						'type' => ['array', 'null'],
						'items' => [
							'type' => 'string'
						]
					]
				],
				'required' => ['user']
			]
		]
	],
	function($req, $module_data) {
		$new = NULL;
		$pass = NULL;

		$user = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIJSONValidatorModule'];

		if (!$user->is_in_group('admin')) {
			throw new APIException(
				'Not authorized as non-admin.',
				HTTPStatus::UNAUTHORIZED
			);
		}
		if (User::exists($params->user)) {
			throw new APIException(
				"User '{$params->user}' already exists.",
				HTTPStatus::BAD_REQUEST
			);
		}

		$new = new User();

		// Set name.
		try {
			$new->set_name($params->user);
		} catch (ArgException $e) {
			throw new APIException(
				'Invalid username',
				HTTPStatus::BAD_REQUEST,
				$e
			);
		}

		// Set groups.
		if (property_exists($params, 'groups')) {
			try {
				$new->set_groups($params->groups);
			} catch (ArgException $e) {
				throw new APIException(
					'Invalid groups.',
					HTTPStatus::BAD_REQUEST,
					$e
				);
			}
		}

		// Generate password.
		try {
			$pass = Util::gen_passwd(Config::config('GENERATED_PASSWD_LEN'));
			$new->set_password($pass);
		} catch (\Exception $e) {
			throw new APIException(
				"Failed to generate password.",
				HTTPStatus::INTERNAL_SERVER_ERROR,
				$e
			);
		}

		// Write to file.
		try {
			$new->write();
		} catch (LimitException $e) {
			throw new APIException(
				'Too many users.',
				HTTPStatus::FORBIDDEN
			);
		} catch (\Exception $e) {
			throw new APIException(
				'Failed to write userdata.',
				HTTPStatus::INTERNAL_SERVER_ERROR
			);
		}

		return [
			'user' => $new->export(FALSE, FALSE),
			'pass' => $pass
		];
	}
);
