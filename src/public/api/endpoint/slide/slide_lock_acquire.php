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
*  The 'error' value returned by this endpoint is
*
*    * API_E_OK if the slide locking succeeds.
*    * API_E_LOCK if the slide is already locked by another user.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * id = The ID of the slide to lock.
*
*  Return value
*    * lock  = Slide lock data.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');

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
					'id' => [
						'type' => 'string'
					]
				],
				'required' => ['id']
			]
		]
	],
	function($req, $resp, $module_data) {
		$params = $module_data['APIJsonValidatorModule'];
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
			throw new APIException(API_E_NOT_AUTHORIZED, "Not authorized");
		}

		try {
			$slide->lock_acquire($session);
		} catch (SlideLockException $e) {
			throw new APIException(
				API_E_LOCK,
				"Failed to lock slide.",
				0,
				$e
			);
		}
		$slide->write();

		return ['lock' => $slide->get_lock()->export(FALSE, FALSE)];
	}
);
