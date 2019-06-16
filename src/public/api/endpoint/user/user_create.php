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
*    * user
*
*      * name   = The name of the user.
*      * groups = The groups the user is in.
*      * pass   = The generated cleartext password.
*
*    * error = An error code or API_E_OK on success.
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
		$new = NULL;
		$pass = NULL;

		$user = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIJsonValidatorModule'];

		if (!$user->is_in_group('admin')) {
			throw new APIException(API_E_NOT_AUTHORIZED, "Not authorized.");
		}
		if (user_exists($params->user)) {
			throw new APIException(API_E_INVALID_REQUEST, "User already exists.");
		}

		$new = new User();

		// Set name.
		try {
			$new->set_name($params->user);
		} catch (ArgException $e) {
			throw new APIException(
				API_E_LIMITED,
				"Limited.", 0, $e
			);
		}

		// Set groups.
		if (property_exists($params, 'groups')) {
			try {
				$new->set_groups($params->groups);
			} catch (ArgException $e) {
				throw new APIException(
					API_E_LIMITED,
					"Limited.", 0, $e
				);
			}
		}

		// Generate password.
		try {
			$pass = gen_passwd(GENERATED_PASSWD_LEN);
		} catch (Exception $e) {
			throw new APIException(
				API_E_INTERNAL,
				"Failed to generate password.", 0, $e
			);
		}
		$new->set_password($pass);

		// Write to file.
		if ($new->write() === FALSE) {
			throw new APIException(API_E_LIMITED, "Too many users.");
		}

		return [
			'user' => $new->export(FALSE, FALSE),
			'pass' => $pass
		];
	}
);
