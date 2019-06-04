<?php
/*
*  ====>
*
*  Get the data of a slide.
*
*  **Request:** GET
*
*  Parameters
*    * id = The id of the slide to get.
*
*  Return value
*    * slide
*
*      * id            = The ID of the slide.
*      * name          = The name of the slide.
*      * index         = The index of the slide.
*      * duration      = The duration of the slide.
*      * markup        = The markup of the slide.
*      * owner         = The owner of the slide.
*      * enabled       = Whether the slide is enabled or not.
*      * sched         = Whether the slide is scheduled or not.
*      * sched_t_s     = The slide schedule starting timestamp.
*      * sched_t_e     = The slide schedule ending timestamp.
*      * animation     = The slide animation identifier.
*      * collaborators = The collaborators of the slide.
*      * lock          = Slide lock data object.
*      * assets        = Slide assets data object.
*
*    * error   = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');

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
					'id' => [
						'type' => 'string'
					]
				],
				'required' => ['id']
			]
		]
	],
	function($req, $resp, $module_data) {
		$params = $module_data['APIQueryValidatorModule'];
		$slide = new Slide();
		$slide->load($params->id);
		return ['slide' => $slide->export(FALSE, FALSE)];
	}
);
