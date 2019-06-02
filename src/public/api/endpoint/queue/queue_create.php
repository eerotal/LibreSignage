<?php
/*
*  ====>
*
*  Create a slide queue.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * name = Queue name.
*
*  Return value
*    * error  = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/queue.php');

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
					'name' => [
						'type' => 'string'
					]
				],
				'required' => ['name']	
			]
		]
	],
	function($req, $resp, $module_data) {
		$queue = NULL;
		$caller = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIJsonValidatorModule'];

		if (!$caller->is_in_group('admin') && !$caller->is_in_group('editor')) {
			throw new APIException(API_E_NOT_AUTHORIZED, 'Not authorized.');
		}

		if (queue_exists($params->name)) {
			throw new APIException(API_E_INVALID_REQUEST, 'Queue already exists.');
		}

		$queue = new Queue($params->name);
		$queue->set_owner($caller->get_name());
		$queue->write();

		return [];
	}
);
