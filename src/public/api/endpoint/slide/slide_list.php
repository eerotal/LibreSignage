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

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \api\APIEndpoint;
use \common\php\slide\Slide;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $params, $module_data) {
		return ['slides' => Slide::list_ids()];
	}
);
