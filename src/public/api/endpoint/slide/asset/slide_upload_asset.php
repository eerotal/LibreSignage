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
*    * error         = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');
require_once(Config::config('LIBRESIGNAGE_ROOT').'/api/APIInterface.php');
require_once(Config::config('LIBRESIGNAGE_ROOT').'/common/php/slide/slide.php');

$SLIDE_UPLOAD_ASSET = new APIEndpoint([
	APIEndpoint::METHOD         => API_METHOD['POST'],
	APIEndpoint::REQUEST_TYPE   => API_MIME['multipart/form-data'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY    => [ 'id' => API_P_STR ],
	APIEndpoint::REQ_QUOTA      => TRUE,
	APIEndpoint::REQ_AUTH       => TRUE
]);

$slide = new Slide();
$slide->load($SLIDE_UPLOAD_ASSET->get('id'));

// Allow admins, slide owners or slide collaborators to upload assets.
if (!(
	check_perm(
		'grp:admin;',
		$SLIDE_UPLOAD_ASSET->get_caller()
	)
	|| check_perm(
		'grp:editor&usr:'.$slide->get_owner().';',
		$SLIDE_UPLOAD_ASSET->get_caller())
	|| (
		check_perm('grp:editor;', $SLIDE_UPLOAD_ASSET->get_caller())
		&& in_array(
			$SLIDE_UPLOAD_ASSET->get_caller()->get_name(),
			$slide->get_owner()
		)
	)
)) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		'Not authorized.'
	);
}

$errors = [];
foreach($SLIDE_UPLOAD_ASSET->get_file_data() as $f) {
	if ($f['error'] !== UPLOAD_ERR_OK) {
		$errors[$f['name']] = $f['error'];
		continue;
	}
	if ($slide->has_uploaded_asset($f['name'])) {
		$errors[$f['name']] = -4;
		continue;
	}
	try {
		$slide->store_uploaded_asset($f);
	} catch (IntException $e) {
		$errors[$f['name']] = -1;		
	} catch (ArgException $e) {
		$errors[$f['name']] = -2;
	} catch (FileTypeException $e) {
		$errors[$f['name']] = -3;
	} catch (LimitException $e) {
		$errors[$f['name']] = -5;
	}
}
$slide->write();

$resp = [
	'failed' => count($errors),
	'error' => count($errors) !== 0 ? API_E_UPLOAD : API_E_OK
];
if (API_ERROR_TRACE) { $resp['upload_errors'] = $errors; }

$SLIDE_UPLOAD_ASSET->resp_set($resp);
$SLIDE_UPLOAD_ASSET->send();
