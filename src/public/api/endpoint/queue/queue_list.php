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

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\Queue;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $module_data) {
		return ['queues' => Queue::list()];
	}
);
