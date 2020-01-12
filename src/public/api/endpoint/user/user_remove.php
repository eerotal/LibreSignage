<?php
/** \file
* Remove a user.
*
* @method{POST}
* @auth{By token}
* @groups{admin}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,user,The name of the user to remove.,required}
* @request_end
*
* @status_start
* @status{200,On success.}
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
					]
				],
				'required' => ['user']
			]
		]
	],
	function($req, $module_data) {
		$caller = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIJSONValidatorModule'];

		if (!$caller->is_in_group('admin')) {
			throw new APIException(
				'Not authorized as a non-admin user.',
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

		try {
			$u->remove();
		} catch (IntException $e) {
			throw new APIException(
				"Failed to remove user '{$params->user}'.",
				HTTPStatus::INTERNAL_SERVER_ERROR,
				$e
			);
		}

		return [];
	}
);
