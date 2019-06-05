<?php
/*
*  ====>
*
*  Remove a slide.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * id = The id of the slide to remove.
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
		$params = $module_data['APIJsonValidatorModule'];

		$slide = new Slide();
		$slide->load($params->id);
		$owner = new User($slide->get_owner());

		if (
			!$caller->is_in_group('admin')
			&& (
				!$caller->is_in_group('editor')
				|| $caller->get_name() !== $slide->get_owner()
			)
		) {
			throw new APIException(API_E_NOT_AUTHORIZED, "Not authorized.");
		}

		$slide->remove();

		// Normalize slide indices now that one is left unused.
		$queue = new Queue($slide->get_queue_name());
		$queue->load();
		$queue->normalize();

		$owner->get_quota()->free_quota('slides');
		$owner->write();

		return [];
	}
);
