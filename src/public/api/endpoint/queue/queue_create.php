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
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIInterface.php');
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
			throw new APIException(
				'User not in groups admin or editor not authorized.',
				HTTPStatus::UNAUTHORIED
			);
		}

		if (queue_exists($params->name)) {
			throw new APIException(
				'Queue already exists.',
				HTTPStatus::BAD_REQUEST
			);
		}

		$queue = new Queue($params->name);
		$queue->set_owner($caller->get_name());
		$queue->write();

		return [];
	}
);
