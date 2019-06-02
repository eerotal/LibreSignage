<?php
/*
*  ====>
*
*  Get a slide queue.
*
*  **Request:** GET
*
*  Parameters
*    * name = The name of the queue to get.
*
*  Return value
*    * owner  = The owner of the queue.
*    * slides = A list containing the IDs of the slides in the queue.
*    * error  = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/queue.php');

APIEndpoint::GET(
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
		$params = $module_data['APIJsonValidatorModule'];
		$queue = new Queue($params->name);
		$queue->load();
		return $queue->get_data_array();
	}
);
