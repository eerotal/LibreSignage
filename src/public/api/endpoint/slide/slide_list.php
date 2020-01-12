<?php
/** \file
* Get the existing slide IDs as an array.
*
* @method{GET}
* @auth{By token}
* @groups{admin|editor|display}
* @ratelimit_yes
*
* @response_start{application/json}
* @response{array,slides,An array of slide IDs.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status_end
*/

namespace libresignage\api\endpoint\slide;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\common\php\slide\Slide;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $module_data) {
		return ['slides' => Slide::list_ids()];
	}
);
