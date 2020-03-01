<?php
/** \file
* Create a new user.
*
* @method{POST}
* @auth{By token}
* @groups{admin}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,user,The name of the new user.,required}
* @request{array|NULL,groups,An array of groups or NULL for no groups.,optional}
* @request{boolean,no_login,Allow login without a password.,optional}
* @request_end
*
* @response_start{application/json}
* @response{User,user,The new User object.}
* @response{string,pass,The random password of the new user or
*                       NULL if no login is required.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{401,If the caller is not allowed to create users.}
* @status{403,If the maximum number of users is reached.}
* @status_end
*/

namespace libresignage\api\endpoint\user;

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
					],
					'passwordless' => [
						'type' => 'boolean'
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

		// Generate a password if login is required for the user.
		if (
			!property_exists($params, 'passwordless')
			|| !$params->passwordless
		) {
			try {
				$pass = Util::gen_passwd(
					Config::config('GENERATED_PASSWD_LEN')
				);
				$new->set_password($pass);
			} catch (\Exception $e) {
				throw new APIException(
					"Failed to generate password.",
					HTTPStatus::INTERNAL_SERVER_ERROR,
					$e
				);
			}
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
