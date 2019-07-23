<?php
/** \file
* Get version information.
*
* @method{GET}
* @auth{Not required}
* @groups{N/A}
* @ratelimit_no
*
* @response_start{application/json}
* @response{string,ls,The LibreSignage version string.}
* @response{string,api,The LibreSignage API version string.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status_end
*/

namespace libresignage\api\endpoint\general;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\common\php\Config;
use libresignage\api\APIEndpoint;

APIEndpoint::GET(
	[],
	function($req, $module_data) {
		return [
			'ls' => Config::config('LS_VER'),
			'api' => Config::config('API_VER')
		];
	}
);
