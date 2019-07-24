<?php
/** \file
* Get queue data.
*
* @method{GET}
* @auth{By token}
* @groups{admin|editor|display}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,name,The name of the queue to fetch.,required}
* @request_end
*
* @response_start{application/json}
* @response{Queue,queue,The requested queue object.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{404,If the requested queue doesn't exist.}
* @status_end
*/

namespace libresignage\api\endpoint\queue;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\queue\Queue;
use libresignage\common\php\queue\exceptions\QueueNotFoundException;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => [],
		'APIQueryValidatorModule' => [
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
		$params = $module_data['APIQueryValidatorModule'];

		$queue = new Queue();
		try {
			$queue->load($params->name);
		} catch(QueueNotFoundException $e) {
			throw new APIException(
				"Queue '{$params->name}' doesn't exist.",
				HTTPStatus::NOT_FOUND,
				$e
			);
		}

		return ['queue' => $queue->export(FALSE, FALSE)];
	}
);
