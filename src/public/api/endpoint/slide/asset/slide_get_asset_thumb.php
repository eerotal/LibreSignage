<?php
/** \file
* Get an asset thumbnail of a slide.
*
* You can (and probably should) pass the asset hash in the 'hash'
* request parameter to prevent problems loading cached versions of
* asset thumbs when the asset has changed. The server does send the proper
* ETag headers but HTML <img> tags, for example, don't seem to obey them.
*
* @method{GET}
* @auth{By cookie or token}
* @groups{admin|editor|display}
* @ratelimit_yes
* @cache_yes
*
* @request_start{application/json}
* @request{string,id,The ID of the slide.,required}
* @request{string,name,The name of the asset.,required}
* @request{string,hash,The asset hash (see description).,optional}
* @request_end
*
* @response_start{The requested asset.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{400,If the request parameters are invalid.}
* @status{401,If the caller is not allowed to get asset thumbnails.}
* @status{404,If the asset doesn't exist.}
* @status{404,If the asset doesn't have a thumbnail.}
* @status{404,If the slide doesn't exist.}
* @status_end
*/

namespace libresignage\api\endpoint\slide\asset;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\common\php\Config;
use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;
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
					'name' => ['type' => 'string'],
					'hash' => ['type' => 'string']
				],
				'required' => ['id', 'name']
			]
		]
	],
	function($req, $module_data) {
		$response = NULL;
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
		try {
			$slide->load($params->id);
		} catch (SlideNotFoundException $e) {
			throw new APIException(
				"Slide '{$params->id}' doesn't exist.",
				HTTPStatus::NOT_FOUND,
				$e
			);
		}

		try {
			$asset = $slide->get_uploaded_asset($params->name);
		} catch (ArgException $e) {
			throw new APIException(
				"Asset '{$params->name}' doesn't exist.",
				HTTPStatus::NOT_FOUND
			);
		}

		if (!$asset->has_thumb()) {
			throw new APIException(
				"Asset doesn't have a thumbnail.",
				HTTPStatus::NOT_FOUND
			);
		}

		/*
		* Create the BinaryFileResponde and set the proper cache headers.
		* Note that the Cache-Control: no-cache directive is set so that
		* the client always validates the cached data before displaying it.
		* This way images are always up-to-date.
		*/
		$response = new BinaryFileResponse($asset->get_internal_thumb_path());
		$last_modified = (new \DateTime())->setTimestamp($asset->get_mtime());

		$response
			->setEtag($asset->get_hash())
			->setLastModified($last_modified)
			->setPrivate()
			->headers->addCacheControlDirective('no-cache');
		$response->isNotModified($req);

		return $response;
	}
);
