<?php
/*
*  ====>
*
*  Login using the authentication system.
*
*  **Request:** POST, application/json
*
*  POST parameters
*    * username    = Username
*    * password    = Password
*    * who         = A string that identifies the caller.
*    * permanent   = If TRUE, create a permanent session.
*
*  Return value
*    * user    = Current user data.
*    * session = Current session data.
*    * token   = Current session token.
*
*  <====
*/

namespace pub\api\endpoints\auth;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \common\php\Config;
use \api\APIEndpoint;
use \api\APIException;
use \api\HTTPStatus;
use \common\php\auth\Auth;

APIEndpoint::POST(
	[
		'APIJSONValidatorModule' => [
			'schema' => [
				'type' => 'object',
				'properties' => [
					'username' => ['type' => 'string'],
					'password' => ['type' => 'string'],
					'who' => ['type' => 'string'],
					'permanent' => ['type' => 'boolean']
				],
				'required' => ['username', 'password', 'who', 'permanent']
			]
		]
	],
	function($req, $resp, $module_data) {
		$params = $module_data['APIJSONValidatorModule'];

		$user = Auth::verify_creds($params->username, $params->password);
		if ($user === NULL) {
			throw new APIException(
				'Invalid credentials.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$data = $user->session_new(
			$params->who,
			$req->getClientIp(),
			$params->permanent
		);
		$user->write();

		// Set the session token cookie.
		setcookie(
			$name = 'session_token',
			$value = $data['token'],
			$expire = Config::config('PERMACOOKIE_EXPIRE'),
			$path = '/'
		);

		return [
			'user' => $user->export(FALSE, FALSE),
			'session' => $data['session']->export(FALSE, FALSE),
			'token' => $data['token']
		];
	}
);
