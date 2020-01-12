<?php
/** \file
* Log in to the API.
*
* @method{POST}
* @auth{Not required}
* @groups{admin|editor|display}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,username,The username to use for login.,required}
* @request{string,password,The password to use for login.,required}
* @request{string,who,A string that identifies the caller.,required}
* @request{bool,permanent,Whether to create a permanent session.,required}
* @request_end
*
* @response_start{application/json}
* @response{User,user,Current user data.}
* @response{Session,session,Current session data.}
* @response{string,token,Current session token.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{401,On invalid credentials.}
* @status{400,If the request parameters are invalid.}
* @status_end
*
*/

namespace libresignage\api\endpoint\auth;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\common\php\Config;
use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\auth\Auth;

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
	function($req, $module_data) {
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
