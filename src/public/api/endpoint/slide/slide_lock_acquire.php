<?php
/** \file
* Attempt to lock a slide.
*
* The operation is permitted if the following conditions are met:
*
*   * The caller is in the 'admin' or 'editor' groups.
*   * The slide is not already locked by another user.
*   * The user has modification permissions for the slide.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,id,The ID of the slide to lock.,required}
* @request_end
* 
* @response_start{application/json}
* @response{Lock,lock,The created Lock object.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{401,If the caller is not allowed to lock the slide.}
* @status{404,If the slide doesn't exist.}
* @status{423,If the slide is already locked by another user.}
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
		$params = $module_data['APIJSONValidatorModule'];
		$caller = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

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

		if (
			!$slide->can_modify($caller)
			|| !$caller->is_in_group(['admin', 'editor'])
		) {
			throw new APIException(
				"Operation not permitted for user.",
				HTTPStatus::UNAUTHORIZED
			);
		}

		try {
			$slide->lock_acquire($session);
		} catch (SlideLockException $e) {
			throw new APIException(
				'Failed to lock slide.',
				HTTPStatus::LOCKED,
				$e
			);
		}
		$slide->write();

		return ['lock' => $slide->get_lock()->export(FALSE, FALSE)];
	}
);
