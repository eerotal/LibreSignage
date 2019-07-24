<?php
/** \file
* Get the existing queue names as an array.
*
* @method{GET}
* @auth{By token}
* @groups{admin|editor|display}
* @ratelimit_yes
*
* @response_start{application/json}
* @response{array,queues,The queue names as an array.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status_end
*/
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

namespace libresignage\api\endpoint\queue;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\queue\Queue;

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
