<?php
/*
*  ====>
*
*  Attempt to lock a slide.
*
*  The operation is permitted if the following conditions are met:
*
*    * The caller is in the 'admin' or 'editor' groups.
*    * The slide is not already locked by another user.
*    * The user has modification permissions for the slide.
*
*  The HTTP status returned by this endpoint is
*
*    * '200 OK' if the slide locking succeeds.
*    * '423 Locked' if the slide is already locked by another user.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * id = The ID of the slide to lock.
*
*  Return value
*    * lock  = Slide lock data.
*
*  <====
*/

namespace pub\api\endpoints\slide;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \api\APIEndpoint;
use \api\APIException;
use \api\HTTPStatus;
use \common\php\slide\Slide;
use \common\php\slide\SlideLockException;

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
	function($req, $resp, $module_data) {
		$params = $module_data['APIJSONValidatorModule'];
		$caller = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

		$slide = new Slide();
		$slide->load($params->id);

		if (
			!$slide->can_modify($caller)
			|| (
				!$caller->is_in_group('admin')
				&& !$caller->is_in_group('editor')
			)
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
