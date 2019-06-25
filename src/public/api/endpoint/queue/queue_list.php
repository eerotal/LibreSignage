<?php
/*
*  ====>
*
*  Get a list of the existing slide queue names.
*
*  **Request:** GET
*
*  Return value
*    * queues = A list containing the slide queue names.
*
*  <====
*/

namespace pub\api\endpoints\queue;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/queue.php');
use \api\APIEndpoint;


APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $resp, $module_data) {
		return ['queues' => queue_list()];
	}
);
