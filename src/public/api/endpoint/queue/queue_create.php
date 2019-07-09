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

namespace pub\api\endpoints\queue;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \api\APIEndpoint;
use \api\APIException;
use \api\HTTPStatus;
use \common\php\slide\Slide;
use \common\php\Queue;

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
		$params = $module_data['APIJSONValidatorModule'];

		if (!$caller->is_in_group('admin') && !$caller->is_in_group('editor')) {
			throw new APIException(
				'User not in groups admin or editor not authorized.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		if (Queue::exists($params->name)) {
			throw new APIException(
				'Queue already exists.',
				HTTPStatus::BAD_REQUEST
			);
		}

		$queue = new Queue();
		$queue->set_name($params->name);
		$queue->set_owner($caller->get_name());
		$queue->write();

		return [];
	}
);
