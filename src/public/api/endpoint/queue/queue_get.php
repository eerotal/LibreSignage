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
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIInterface.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/queue.php');

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
	function($req, $resp, $module_data) {
		$params = $module_data['APIQueryValidatorModule'];
		$queue = new Queue($params->name);
		$queue->load();
		return $queue->get_data_array();
	}
);
