<?php
/** \file
* Duplicate a slide.
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
* @request{string,id,The ID of the slide to duplicate.,required}
* @request_end
*
* @response_start{application/json}
* @response{Slide,slide,The duplicated slide object.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{401,If the user is not allowed to duplicate slides.}
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
					'id' => [
						'type' => 'string'
					]
				],
				'required' => ['id']
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

		$new = $old->dup();
		$new->set_owner($caller->get_name());
		$new->lock_acquire($session);
		$new->write();

		// Juggle slide indices to make sure they are correct.
		$queue = $new->get_queue();
		$queue->juggle($new->get_id());
		$queue->write();

		return ['slide' => $new->export(FALSE, FALSE)];
	}
);
