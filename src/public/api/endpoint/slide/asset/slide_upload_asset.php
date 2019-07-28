<?php
/** \file
* Upload a slide asset.
*
* If debugging is enabled, the \c upload_errors field of the returned
* data contains an error code for all failed uploads. Positive integers
* indicate PHP upload errors and negative integers are LibreSignage
* specific errors. Below is a list of the negative error codes.
*
* * -1 = \c move_uploaded_file() failed.
* * -2 = Asset filename invalid.
* * -3 = Invalid asset file type.
* * -4 = Asset already exists.
* * -5 = Too many slide assets.
*
* [You can find the PHP errors here.](http://php.net/manual/en/features.file-upload.errors.php)
*
* This API endpoint only accepts asset filenames that have a length
* less than or equal to the server limit ``SLIDE_ASSET_NAME_MAX_LEN``.
* The asset names may only contain the characters A-Z, a-z, 0-9,
* ., _ and -. The accepted filetypes are defined in the server limit
* ``SLIDE_ASSET_VALID_MIMES``.
*
* @method{POST}
* @auth{By token}
* @groups{admin|editor}
* @ratelimit_yes
*
* @par Parameters
* multipart/form-data
* * ``file``  **0...n** The file(s) to upload. [*required*]
* * ``string`` **body** The JSON encoded request body. See below. [*required*]
*
* @par JSON body
* application/json
* * ``string`` **id** The ID of the slide. [*required*]
*
* @response_start{application/json}
* @response{int,failed,The number of failed uploads.}
* @response_end
*
* @status_start
* @status{200,On success.}
* @status{401,If the caller is not allowed to upload assets to the slide.}
* @status{404,If the slide doesn't exist.}
* @status{424,If the slide is not locked.}
* @status{424,If the slide is locked by another session}
* @status_end
*/

namespace libresignage\api\endpoint\slide\asset;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\slide\exceptions\SlideNotFoundException;
use libresignage\common\php\exceptions\ArgException;
use libresignage\common\php\exceptions\FileTypeException;
use libresignage\common\php\exceptions\LimitException;

const UPLOAD_ERR_EXISTS          = -1;
const UPLOAD_ERR_INTERNAL        = -2;
const UPLOAD_ERR_BAD_REQUEST     = -3;
const UPLOAD_ERR_BAD_FILETYPE    = -4;
const UPLOAD_ERR_TOO_MANY_ASSETS = -5;

APIEndpoint::POST(
	[
		'APIAuthModule' => ['cookie_auth' => FALSE],
		'APIRateLimitModule' => [],
		'APIMultipartRequestValidatorModule' => [
			'schema' => [
				'type' => 'object',
				'properties' => [
					'id' => ['type' => 'string']
				],
				'required' => ['id']
			]
		]
	],
	function($req, $module_data) {
		$slide = NULL;
		$lock = NULL;
		$errors = [];

		$params = $module_data['APIMultipartRequestValidatorModule'];
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
				'User not allowed to upload assets to this slide.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		if (!$slide->is_locked_by($session)) {
			throw new APIException(
				'Slide not locked by the calling session.',
				HTTPStatus::FAILED_DEPENDENCY
			);
		}

		foreach ($req->files->all() as $f) {
			$name = $f->getClientOriginalName();

			if ($f->getError() !== UPLOAD_ERR_OK) {
				$errors[$name] = $f->getError();
				continue;
			} else if ($slide->has_uploaded_asset($name)) {
				$errors[$name] = UPLOAD_ERR_EXISTS;
				continue;
			}

			try {
				$slide->store_uploaded_asset($f);
			} catch (ArgException $e) {
				$errors[$name] = UPLOAD_ERR_BAD_REQUEST;
			} catch (FileTypeException $e) {
				$errors[$name] = UPLOAD_ERR_BAD_FILETYPE;
			} catch (LimitException $e) {
				$errors[$name] = UPLOAD_ERR_TOO_MANY_ASSETS;
			} catch (\Exception $e) {
				$errors[$name] = UPLOAD_ERR_INTERNAL;
			}
		}
		$slide->write();

		return [
			'failed' => count($errors),
			'upload_errors' => $errors
		];
	}
);
