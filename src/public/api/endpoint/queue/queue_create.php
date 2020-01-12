<?php
/** \file
* Create a new queue.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,name,The name of the new queue.,required}
* @request_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{400,If the queue already exists.}
* @status{401,If the caller is not allowed to create queues.}
* @status_end
*/

namespace libresignage\api\endpoint\queue;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\queue\Queue;

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
	function($req, $module_data) {
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
