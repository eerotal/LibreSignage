<?php
/** \file
* Remove a slide.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,id,The ID of the slide to remove.,required}
* @request_end
*
* @status_start
* @status{200,On success.},
* @status{400,If the request parameters are invalid.}
* @status{401,If the caller is not allowed to remove the slide.}
* @status{404,If the slide doesn't exist.}
* @status{424,If the slide is not locked by the calling session.}
* @status_end
*/

namespace libresignage\api\endpoint\slide;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;
use libresignage\common\php\queue\Queue;
use libresignage\common\php\queue\exceptions\QueueNotFoundException;
use libresignage\common\php\auth\User;

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

		$owner = new User();
		try {
			$owner->load($slide->get_owner());
		} catch (UserNotFoundException $e) {
			throw new APIException(
				"Owner '{$slide->get_owner()}' of '{$slide->get_id()}' ".
				"doesn't exist. This shouldn't happen.",
				HTTPStatus::INTERNAL_SERVER_ERROR,
				$e
			);
		}

		if (
			!$caller->is_in_group('admin')
			&& (
				!$caller->is_in_group('editor')
				|| $caller->get_name() !== $slide->get_owner()
			)
		) {
			throw new APIException(
				'Not authorized because user is not either in the group '.
				'admin or owner of the slide and in the group editor.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		if (!$slide->is_locked_by($session)) {
			throw new APIException(
				'Slide not locked by the calling session.',
				HTTPStatus::FAILED_DEPENDENCY
			);
		}

		$slide->remove();

		$owner->get_quota()->free_quota('slides');
		$owner->write();

		return [];
	}
);
