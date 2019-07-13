<?php
/*
*  ====>
*
*  Get a slide asset.
*
*  **Request:** GET
*
*  JSON parameters
*    * id   = The ID of the slide to access.
*    * name = The name of the asset.
*
*  Return value
*    * The requested asset.
*
*  <====
*/

namespace pub\api\endpoint\slide\asset;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \Symfony\Component\HttpFoundation\BinaryFileResponse;
use \api\APIEndpoint;
use \api\APIException;
use \api\HTTPStatus;
use \common\php\slide\Slide;
use \common\php\exceptions\ArgException;

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => TRUE
		],
		'APIRateLimitModule' => [],
		'APIQueryValidatorModule' => [
			'schema' => [
				'type' => 'object',
				'properties' => [
					'id' => ['type' => 'string'],
					'name' => ['type' => 'string']
				],
				'required' => ['id', 'name']
			]
		]
	],
	function($req, $resp, $module_data) {
		$params = $module_data['APIQueryValidatorModule'];
		$caller = $module_data['APIAuthModule']['user'];

		if (!$caller->is_in_group(['admin', 'editor'])) {
			throw new APIException(
				'User not in admin or editor groups.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$slide = new Slide;
		$slide->load($params->id);

		try {
			$asset = $slide->get_uploaded_asset($params->name);
		} catch (ArgException $e) {
			throw new APIException('No such asset.', HTTPStatus::NOT_FOUND);
		}

		return new BinaryFileResponse($asset->get_fullpath());
	}
);
