<?php
/** \file
* Copy a slide.
*
* The caller is set as the owner of the new slide and the slide is
* automatically locked for the caller.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,id,The ID of the slide to copy.,required}
* @request{string,dest,The name of the destination Queue.,required}
* @request_end
*
* @response_start{application/json}
* @response{Slide,slide,The copied slide object.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{401,If the user is not allowed to copy slides.}
* @status{404,If the requested slide doesn't exist.}
* @status_end
*/

namespace libresignage\api\endpoint\slide;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;
use libresignage\common\php\queue\Queue;
use libresignage\common\php\queue\exceptions\QueueNotFoundException;

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
					'id' => [ 'type' => 'string' ],
					'dest' => [ 'type' => 'string' ]
				],
				'required' => ['id', 'dest']
			]
		]
	],
	function($req, $module_data) {
		$new = NULL;
		$old = NULL;
		$queue = NULL;

		$params = $module_data['APIJSONValidatorModule'];
		$caller = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

		if (!$caller->is_in_group(['admin', 'editor'])) {
			throw new APIException(
				'Not authorized for users not in the groups admin or editor.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$old = new Slide();
		try {
			$old->load($params->id);
		} catch (SlideNotFoundException $e) {
			throw new APIException(
				"Slide '{$params->id}' not found.",
				HTTPStatus::NOT_FOUND,
				$e
			);
		}

		$dest = new Queue();
		try {
			$dest->load($params->dest);
		} catch (QueueNotFoundException $e) {
			throw new APIException(
				"Queue '{$params->dest}' not found.",
				HTTPStatus::NOT_FOUND,
				$e
			);
		}
		$new = $old->copy();
		$new->set_owner($caller->get_name());
		$new->lock_acquire($session);
		$dest->add_slide($new, Queue::ENDPOS);
		$new->write();
		$dest->write();

		return $new->export(FALSE, FALSE);
	}
);
