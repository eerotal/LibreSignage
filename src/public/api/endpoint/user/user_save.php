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
	function($req, $resp, $module_data) {
		$caller = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIJsonValidatorModule'];

		$auth_admin = $caller->is_in_group('admin');
		$auth_user = $caller->get_name() === $params->user;

		// Check for authorization.
		if (!$auth_admin && !$auth_usr) {
			throw new APIException(
				API_E_NOT_AUTHORIZED,
				"Not authorized to modify the userdata."
			);
		}

		try {
			$u = new User($params->user);
		} catch (ArgException $e) {
			throw new APIException(
				API_E_INVALID_REQUEST,
				"Failed to load user.", 0, $e
			);
		}

		// Case 1.
		if (isset($params->pass)) {
			if ($auth_user) {
				try {
					$u->set_password($params->pass);
				} catch (ArgException $e) {
					throw new APIException(
						API_E_LIMITED,
						"Failed to set password.", 0, $e
					);
				}
			} else {
				throw new APIException(
					API_E_NOT_AUTHORIZED,
					"Admin users can't change passwords of other users."
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
						API_E_LIMITED,
						"Failed to set user groups.", 0, $e
					);
				}
			} else {
				throw new APIException(
					API_E_NOT_AUTHORIZED,
					"Non-admin users can't set groups."
				);
			}
		}

		if ($u->write() === FALSE) {
			throw new APIException(
				API_E_LIMITED,
				"Failed to write userdata."
			);
		}

		return [
			'user' => [
				'name' => $u->get_name(),
				'groups' => $u->get_groups()
			]
		];
	}
);
