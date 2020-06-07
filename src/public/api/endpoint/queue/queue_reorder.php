<?php
/** \file
 * Reorder the Slides in a Queue.
 *
 * @method{POST}
 * @auth{By token}
 * @groups{admin|editor}
 * @ratelimit_yes
 *
 * @request_start{application/json}
 * @request{string,queue_name,The name of the Queue.}
 * @request{string,slide_id,The ID of the Slide to add.}
 * @request{integer,pos,The position in the Queue where the
 *                      Slide is moved to. -1 (Queue::ENDPOS)
 *                      for last.}
 * @request_end
 *
 * @status_start
 * @status{200,On success.}
 * @status{400,If the $to parameter is invalid.}
 * @status{401,If the caller is not allowed to use this API endpoint.}
 * @status{404,If the requested Queue or Slide doesn't exist or if the Slide
 *             doesn't exist in the requested Queue.}
 * @status_end
 */

namespace libresignage\api\endpoint\queue;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\queue\Queue;
use libresignage\common\php\queue\exceptions\QueueNotFoundException;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;

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
					'queue_name' => ['type' => 'string'],
					'slide_id' => ['type' => 'string'],
					'to' => ['type' => 'integer']
				],
				'required' => ['queue_name', 'slide_id', 'to']
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

		$queue = new Queue();
		try {
			$queue->load($params->queue_name);
		} catch (QueueNotFoundException $e) {
			throw new APIException(
				"Queue '{$params->queue_name}' not found.",
				HTTPStatus::NOT_FOUND
			);
		}

		$slide = new Slide();
		try {
			$slide->load($params->slide_id);
		} catch (SlideNotFoundException $e) {
			throw new APIException(
				"Slide '{$params->slide_id}' not found.",
				HTTPStatus::NOT_FOUND
			);
		}

		// Attempt to reorder the queue-
		try {
			$queue->reorder($slide, $params->to);
		} catch (SlideNotFoundException $e) {
			throw new APIException(
				$e->getMessage(),
				HTTPStatus::NOT_FOUND
			);
		} catch (ArgException $e) {
			throw new APIException(
				$e->getMessage(),
				HTTPStatus::BAD_REQUEST
			);
		}

		$queue->write();

		return [];
	}
);
