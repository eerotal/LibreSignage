<?php
/*
*  ====>
*
*  Get a list of all the existing slides.
*
*  **Request:** GET
*
*  Return value
*    * An array with all the existing slide IDs.
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
		'APIRateLimitModule' => []
	],
	function($req, $params, $module_data) {
		return ['slides' => slides_id_list()];		
	}
);
