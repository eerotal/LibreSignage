<?php
/** \file
* Attempt to lock a slide.
*
* The operation is permitted if the following conditions are met:
*
*   * The caller is in the 'admin' or 'editor' groups.
*   * The slide has previously been locked by the calling session.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor}
* @ratelimit_yes
*
* @request_start{application/json}
* @response{string,id,The ID of the slide to unlock.}
* @request_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{401,If the user is not allowed to unlock the slide.}
* @status{404,If the slide doesn't exist.}
* @status{423,If the slide is locked by another session.}
* @status_end
*/

namespace libresignage\api\endpoint\slide;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\slide\exceptions\SlideLockException;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;

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
					'id' => [
						'type' => 'string'
					]
				],
				'required' => ['id']
			]
		]
	],
	function($req, $module_data) {
		$caller = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];
		$params = $module_data['APIJSONValidatorModule'];

		if (!$caller->is_in_group(['admin', 'editor'])) {
			throw new APIException(
				'Not authorized because user is not admin or editor.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$slide = new Slide();
		try {
			$slide->load($params->id);
		} catch (SlideNotFoundException $e) {
			throw new APIException(
				"Slide '{$params->id}' doesn't exist.",
				HTTPStatus::NOT_FOUND,
				$e
			);
		}

		try {
			$slide->lock_release($session);
		} catch (SlideLockException $e) {
			throw new APIException(
				"Can't release slide lock created from another session.",
				HTTPStatus::LOCKED,
				$e
			);
		}
		$slide->write();

		return [];
	}
);
