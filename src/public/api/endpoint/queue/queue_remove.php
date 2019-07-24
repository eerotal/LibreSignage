<?php
/** \file
* Remove a slide queue and all slides in it.
*
* The operation is allowed if the caller is in the 'admin' group or if
* the caller is in the editor group and owns all the slides in the queue.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,name,The name of the queue to remove.,required}
* @request_end
*
* @status_start
* @status{200,On success.}
* @status{401,If the user is not allowed to remove the queue.}
* @status{400,If the request parameters are invalid.}
* @status{404,If the requested queue doesn't exist.}
* @status_end
*/

namespace libresignage\api\endpoint\queue;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\queue\Queue;
use libresignage\common\php\queue\exceptions\QueueNotFoundException;
use libresignage\common\php\Util;

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
		$caller = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIJSONValidatorModule'];

		$queue = new Queue();
		try {
			$queue->load($params->name);
		} catch (QueueNotFoundException $e) {
			throw new APIException(
				"Queue '{$params->name}' doesn't exist.",
				HTTPStatus::NOT_FOUND,
				$e
			);
		}
		$owner = $queue->get_owner();

		if (
			$caller->is_in_group('admin')
			|| (
				$caller->is_in_group('editor')
				&& Util::array_check($queue->slides(), function($s) use($caller) {
					return $s->get_owner() === $caller->get_name();
				})
			)
		) {
			$queue->remove();
			return [];
		}
		throw new APIException(
			'Non-admin user not authorized.',
			HTTPStatus::UNAUTHORIZED
		);
	}
);
