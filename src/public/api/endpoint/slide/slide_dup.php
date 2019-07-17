<?php
/*
*  ====>
*
*  Duplicate a slide. The owner of the new slide is the caller
*  of this API endpoint. The new slide is also automatically
*  locked for the caller. The operation is authorized if the user
*  is in the 'admin' or 'editor' groups.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * id = The ID of the slide to duplicate.
*
*  Return value
*    * slide = Duplicated slide data. See slide_get.php for more info.
*
*  <====
*/

namespace pub\api\endpoints\slide;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \api\APIEndpoint;
use \api\APIException;
use \api\HTTPStatus;
use \common\php\slide\Slide;

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

		if (!$caller->is_in_group('admin') && !$caller->is_in_group('editor')) {
			throw new APIException(
				'Not authorized for users not in the groups admin or editor.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$old = new Slide();
		$old->load($params->id);

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
