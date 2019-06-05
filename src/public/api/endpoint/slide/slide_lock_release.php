<?php
/*
*  ====>
*
*  Attempt to lock a slide.
*
*  The operation is authorized if the following conditions are met:
*
*    * The caller is in the 'admin' or 'editor' groups.
*    * The slide has previously been locked by the caller.
*
*  The 'error' value returned by this endpoint is
*
*    * API_E_OK on success.
*    * API_E_LOCK if the slide is locked by another user.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * id = The ID of the slide to lock.
*
*  Return value
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
		$caller = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];
		$params = $module_data['APIJsonValidatorModule'];

		if (!$caller->is_in_group('admin') && !$caller->is_in_group('editor')) {
			throw new APIException(API_E_NOT_AUTHORIZED, "Not authorized");
		}

		$slide = new Slide();
		$slide->load($params->id);
		try {
			$slide->lock_release($session);
		} catch (SlideLockException $e) {
			throw new APIException(
				API_E_LOCK,
				"Failed to release slide lock.",
				0,
				$e
			);
		}
		$slide->write();

		return [];
	}
);
