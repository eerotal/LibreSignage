<?php
/*
*  ====>
*
*  Get a slide asset thumbnail.
*
*  **Request:** GET
*
*  JSON parameters
*    * id   = The ID of the slide to access.
*    * name = The name of the asset.
*
*  Return value
*    * The thumbnail data with the correct Content-Type header set
*      on success. If the asset doesn't have a thumbnail, the
*      Content-Length header is set to 0. On failure, the response
*      type is application/json and the JSON contains the key 'error'
*      with the error code assigned to it.
*
*  <====
*/

namespace libresignage\api\endpoint\slide\asset;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\exceptions\ArgException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

APIEndpoint::GET(
	[
		'APIAuthModule' => ['cookie_auth' => TRUE],
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
	function($req, $module_data) {
		$slide = NULL;
		$asset = NULL;

		$params = $module_data['APIQueryValidatorModule'];
		$caller = $module_data['APIAuthModule']['user'];

		if (!$caller->is_in_group(['admin', 'editor', 'display'])) {
			throw new APIException(
				'User not authorized to view thumbnails.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$slide = new Slide();
		$slide->load($params->id);

		try {
			$asset = $slide->get_uploaded_asset($params->name);
		} catch (ArgException $e) {
			throw new APIException(
				'No such asset.',
				HTTPStatus::NOT_FOUND
			);
		}

		if ($asset->has_thumb()) {
			return new BinaryFileResponse($asset->get_internal_thumb_path());
		} else {
			throw new APIException(
				"Asset doesn't have a thumbnail.",
				HTTPStatus::NOT_FOUND
			);
		}
	}
);
