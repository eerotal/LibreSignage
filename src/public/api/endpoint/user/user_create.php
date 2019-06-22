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
			throw new APIException(
				'Not authorized as non-admin.',
				HTTPStatus::UNAUTHORIZED
			);
		}
		if (user_exists($params->user)) {
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
			$pass = gen_passwd(GENERATED_PASSWD_LEN);
		} catch (Exception $e) {
			throw new APIException(
				"Failed to generate password.",
				HTTPStatus::INTERNAL_SERVER_ERROR,
				$e
			);
		}
		$new->set_password($pass);

		// Write to file.
		if ($new->write() === FALSE) {
			throw new APIException(
				'Too many users.',
				HTTPStatus::FORBIDDEN
			);
		}

		return [
			'user' => $new->export(FALSE, FALSE),
			'pass' => $pass
		];
	}
);
