<?php
/** \file
* Remove a slide asset.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor}
* @ratelimit_yes
*
* @request_start{application/json}
* @request{string,id,The ID of the slide.,required}
* @request{string,name,The name of the asset to remove.,required}
* @request_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{401,If the caller is not allowed to remove the asset.}
* @status{404,If the asset doesn't exist.}
* @status{404,If the slide doesn't exist.}
* @status{424,If the slide is not locked by the calling session.}
* @status_end
*/

namespace libresignage\api\endpoint\slide\asset;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;
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
		$session = $module_data['APIAuthModule']['session'];

		$slide = new Slide();
		try {
			$slide->load($params->id);
		} catch (SlideNotFoundException $e) {
			throw new APIException(
				"Slide '{$params->id}' doesn't exist.",
				HTTPStatus::NOT_FOUND,
				$e
			);
		}

		if (!$slide->can_modify($caller)) {
			throw new APIException(
				'User not authorized to remove asset.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		if (!$slide->is_locked_by($session)) {
			throw new APIException(
				'Slide not locked by the calling session.',
				HTTPStatus::FAILED_DEPENDENCY
			);
		}

		try {
			$slide->remove_uploaded_asset($params->name);
		} catch (ArgException $e) {
			throw new APIException(
				"Asset '{$params->name}' doesn't exist.",
				HTTPStatus::NOT_FOUND,
				$e
			);
		}
		$slide->write();

		return [];
	}
);
