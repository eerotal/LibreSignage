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

namespace pub\api\endpoints\slide;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');
use \api\APIEndpoint;

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
