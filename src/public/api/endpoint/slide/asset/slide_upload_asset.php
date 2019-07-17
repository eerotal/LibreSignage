<?php
/*
*  ====>
*
*  Upload a slide asset.
*
*  If debugging is enabled (``API_ERROR_TRACE === TRUE``), the
*  *upload_errors* field of the returned data contains an error code
*  for all failed uploads. Positive integers indicate PHP upload
*  `errors`_ and negative integers are LibreSignage specific errors.
*  Below is a list of the negative error codes.
*
*  * -1 = move_uploaded_file() failed.
*  * -2 = Asset filename invalid.
*  * -3 = Invalid asset file type.
*  * -4 = Asset already exists.
*  * -5 = Too many slide assets.
*
*  This API endpoint only accepts asset filenames that have a length
*  less than or equal to the server limit SLIDE_ASSET_NAME_MAX_LEN.
*  The asset names may only contain the characters A-Z, a-z, 0-9,
*  ., _ and -. The accepted filetypes are defined in the server limit
*  SLIDE_ASSET_VALID_MIMES.
*
*  .. _errors: http://php.net/manual/en/features.file-upload.errors.php
*
*  **Request:** POST, multipart/form-data
*
*  Form-data parameters
*    * file_1 ... file_n = The file(s) to upload.
*    * body = JSON encoded body data.
*      * id = The slide ID to use.
*
*  Return value
*    * failed        = The number of failed uploads.
*    * upload_errors = Error codes for failed uploads.
*
*  <====
*/

namespace pub\api\endpoint\slide\asset;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use \api\APIEndpoint;
use \api\APIException;
use \api\HTTPStatus;
use \common\php\slide\Slide;
use \common\php\Log;

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
		$errors = [];

		$params = $module_data['APIMultipartRequestValidatorModule'];
		$caller = $module_data['APIAuthModule']['user'];

		$slide = new Slide();
		$slide->load($params->id);

		if (!$slide->can_modify($caller)) {
			throw new APIException(
				'User not allowed to upload assets to this slide.',
				HTTPStatus::UNAUTHORIZED
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
			} catch (Exception $e) {
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
