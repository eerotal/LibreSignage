<?php
/*
*  ====>
*
*  Remove a slide asset based on its name.
*
*  **Request:** POST, application/json
*
*  JSON parameters
*    * id   = The ID of the Slide to access.
*    * name = The asset name to remove.
*
*  Return value
*    * error         = An error code or API_E_OK on success.
*
*  <====
*/

namespace pub\api\endpoint\slide\asset;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\exceptions\ArgException;

APIEndpoint::POST(
	[
		'APIAuthModule' => ['cookie_auth' => FALSE],
		'APIRateLimitModule' => [],
		'APIJSONValidatorModule' => [
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

		$params = $module_data['APIJSONValidatorModule'];
		$caller = $module_data['APIAuthModule']['user'];

		$slide = new Slide();
		$slide->load($params->id);

		if (!$slide->can_modify($caller)) {
			throw new APIException(
				'User not authorized to remove asset.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		try {
			$slide->remove_uploaded_asset($params->name);
		} catch (ArgException $e) {
			throw new APIException('No such asset.', HTTPStatus::NOT_FOUND);
		}
		$slide->write();

		return [];
	}
);
