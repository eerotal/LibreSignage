<?php
/** \file
* Save a user.
*
* * The operation is permitted if
*   * The caller is in the group *admin* and *pass* is NULL or unset
*     in the request, ie. the caller is not trying to set the password
*     of the user. This prevents the admin from taking over an account.
*   * The caller is the user that's being saved and *groups* is NULL,
*     unset or equal to the current user groups, ie. the caller is not
*     trying to set groups. This prevents privilege escalation.
*
* @method{POST}
* @auth{By token}
* @groups{ALL}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,user,The name of the user to save.,required}
* @request{string|NULL,pass,The new password of the user.,optional}
* @request{array|NULL,groups,The new groups of the user.,optional}
* @request_end
*
* @response_start{application/json}
* @response{object,user,}
*   @response{string,name,The name of the saved user.}
*   @response{array,groups,The new groups of the user.}
* @response_end
*
* @status_start
* @status{200,On success}
* @status{400,If the request parameters are invalid.}
* @status{401,If the caller is not allowed to save the user data. See above.}
* @status{404,If the user is not found.}
* @status_end
*/

namespace libresignage\api\endpoint\user;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\auth\User;
use libresignage\common\php\auth\exceptions\UserNotFoundException;
use libresignage\common\php\exceptions\IntException;
use libresignage\common\php\exceptions\ArgException;
use libresignage\common\php\exceptions\LimitException;
use libresignage\common\php\Util;

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
		} catch (UserNotFoundException $e) {
			throw new APIException(
				"User '{$params->user}' doesn't exist.",
				HTTPStatus::NOT_FOUND,
				$e
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
		if (
			isset($params->groups)
			&& !Util::set_equals($params->groups, $u->get_groups())
		) {
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
