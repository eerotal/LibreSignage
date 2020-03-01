<?php
/** \file
* Get information about a user. This endpoint only returns data
* that's not secret, ie. no passwords or sessions are returned.
*
* @method{GET}
* @auth{By token}
* @groups{admin}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,user,The name of the user to fetch.,required}
* @request_end
*
* @response_start{application/json}
* @response{string,user,The name of the user.}
* @response{array,groups,The groups of the user.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{401,If the user is not allowed to get information about other users.}
* @status{404,If the user doesn't exist.}
* @status_end
*/

namespace libresignage\api\endpoint\user;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\auth\User;
use libresignage\common\php\auth\exceptions\UserNotFoundException;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => [],
		'APIQueryValidatorModule' => [
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
		$params = $module_data['APIQueryValidatorModule'];
		$user = NULL;

		if (!$caller->is_in_group('admin')) {
			throw new APIException(
				'Not authorized as non-admin.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$user = new User();
		try {
			$user->load($params->user);
		} catch (UserNotFoundException $e) {
			throw new APIException(
				"User '{$params->user}' doesn't exist.",
				HTTPStatus::NOT_FOUND,
				$e
			);
		}

		return [
			'user' => [
				'user' => $user->get_name(),
				'passwordless' => $user->is_passwordless(),
				'groups' => $user->get_groups()
			]
		];
	}
);
