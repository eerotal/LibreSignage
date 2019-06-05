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
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');

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
					'id' => [
						'type' => 'string'
					]
				],
				'required' => ['id']
			]
		]
	],
	function($req, $resp, $module_data) {
		$new = NULL;
		$old = NULL;
		$queue = NULL;

		$params = $module_data['APIJsonValidatorModule'];
		$caller = $module_data['APIAuthModule']['user'];
		$session = $module_data['APIAuthModule']['session'];

		if (!$caller->is_in_group('admin') && !$caller->is_in_group('editor')) {
			throw new APIException(API_E_NOT_AUTHORIZED, "Not authorized");
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
