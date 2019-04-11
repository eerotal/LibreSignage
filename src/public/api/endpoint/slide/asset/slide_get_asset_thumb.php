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

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');

$SLIDE_GET_ASSET_THUMB = new APIEndpoint(array(
	APIEndpoint::METHOD          => API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE   => API_MIME['libresignage/passthrough'],
	APIEndpoint::FORMAT_URL => [
		'id'   => API_P_STR,
		'name' => API_P_STR
	],
	APIEndpoint::REQ_QUOTA         => TRUE,
	APIEndpoint::REQ_AUTH          => TRUE,
	APIEndpoint::ALLOW_COOKIE_AUTH => TRUE
));

if (
	!check_perm(
		'grp:admin|grp:editor|grp:display;',
		$SLIDE_GET_ASSET_THUMB->get_caller()
	)
) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized"
	);
}

$slide = new Slide();
$slide->load($SLIDE_GET_ASSET_THUMB->get('id'));

$asset = $slide->get_uploaded_asset($SLIDE_GET_ASSET_THUMB->get('name'));
if ($asset === NULL) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		'No such asset.'
	);
}

if ($asset->get_thumbpath() === NULL) {
	// No thumbnail exists -> send an empty response.
	header('Content-Type: application/octet-stream');
	header('Content-Length: 0');
	$SLIDE_GET_ASSET_THUMB->send();
} else {
	// Send the asset thumbnail.
	header('Content-Type: '.mime_content_type($asset->get_thumbpath()));
	header('Content-Length: '.filesize($asset->get_thumbpath()));
	$SLIDE_GET_ASSET_THUMB->resp_set(
		fopen($asset->get_thumbpath(), 'rb')
	);
	$SLIDE_GET_ASSET_THUMB->send();
}
