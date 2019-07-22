<?php
/*
*  ====>
*
*  Save a user's data.
*
*  Access is granted in any of the following cases.
*
*    1. The authenticated user is in the group 'admin' and
*       they are not trying to set a new password ie .pass is NULL
*       or unset. This prevents the admin taking over an account.
*    2. The authenticated user is the user to be modified and
*       they are not trying to set user groups ie. groups is NULL
*       or unset. This prevents privilege escalation.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * user    = The user to modify.
*    * pass    = New password (Optionally unset or NULL)
*    * groups  = New groups (Optionally unset or NULL)
*
*  Return value
*    * user
*
*      * name     = The name of the user.
*      * groups   = The groups the user is in.
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
					'pass' => [
						'type' => ['string', 'null']
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
		$caller = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIJSONValidatorModule'];

		$auth_admin = $caller->is_in_group('admin');
		$auth_user = $caller->get_name() === $params->user;

		// Check for authorization.
		if (!$auth_admin && !$auth_user) {
			throw new APIException(
				'Not authorized to modify userdata.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$u = new User();

		try {
			$u->load($params->user);
		} catch (ArgException $e) {
			throw new APIException(
				"Failed to load user '{$params->user}'.",
				HTTPStatus::BAD_REQUEST
			);
		}

		// Case 1.
		if (isset($params->pass)) {
			if ($auth_user) {
				try {
					$u->set_password($params->pass);
				} catch (ArgException $e) {
					throw new APIException(
						'Failed to set password.',
						HTTPStatus::BAD_REQUEST,
						$e
					);
				}
			} else {
				throw new APIException(
					"Admin users can't change passwords of other users.",
					HTTPStatus::UNAUTHORIZED
				);
			}
		}

		// Case 2.
		if (isset($params->groups)) {
			if ($auth_admin) {
				try {
					$u->set_groups($params->groups);
				} catch (ArgException $e) {
					throw new APIException(
						'Failed to set user groups.',
						HTTPStatus::BAD_REQUEST,
						$e
					);
				}
			} else {
				throw new APIException(
					"Non-admin users can't set groups.",
					HTTPStatus::UNAUTHORIZED
				);
			}
		}

		$u->write();

		return [
			'user' => [
				'name' => $u->get_name(),
				'groups' => $u->get_groups()
			]
		];
	}
);
