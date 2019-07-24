<?php
/** \file
* Get the data of a slide.
*
* @method{GET}
* @auth{By token}
* @groups{admin|editor|display}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,id,The ID of the slide to fetch.,required}
* @request_end
*
* @response_start{application/json}
* @response{Slide,slide,The requested slide object.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{404,If the requested slide doesn't exist.}
* @status_end
*/

namespace libresignage\api\endpoint\slide;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;

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
	function($req, $module_data) {
		$params = $module_data['APIQueryValidatorModule'];

		$slide = new Slide();
		try {
			$slide->load($params->id);
		} catch(SlideNotFoundException $e) {
			throw new APIException(
				"Slide '{$params->id}' doesn't exist.",
				HTTPStatus::NOT_FOUND
			);
		}

		return ['slide' => $slide->export(FALSE, FALSE)];
	}
);
