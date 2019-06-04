<?php
/*
*  ====>
*
*  Remove a slide queue and all slides in it. The operation is authorized
*  if the user is in the 'admin' group or if the user is in the editor
*  group and owns all the slides in the queue.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * name = Queue name.
*
*  Return value
*    * error  = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
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
		$caller = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIJsonValidatorModule'];

		$queue = new Queue($params->name);
		$queue->load();
		$owner = $queue->get_owner();

		if (
			$caller->is_in_group('admin')
			|| (
				$caller->is_in_group('editor')
				&& array_check($queue->slides(), function($s) use($caller) {
					return $s->get_owner() === $caller->get_name();
				})
			)
		) {
			$queue->remove();
			return [];
		}
		throw new APIException(API_E_NOT_AUTHORIZED, "Not authorized.");
	}
);
