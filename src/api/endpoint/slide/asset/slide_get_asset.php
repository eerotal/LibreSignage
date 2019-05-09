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
*    * The raw asset data with the correct Content-Type header set.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_GET_ASSET = new APIEndpoint([
	APIEndpoint::METHOD          => API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE   => API_MIME['libresignage/passthrough'],
	APIEndpoint::FORMAT_URL => [
		'id'   => API_P_STR,
		'name' => API_P_STR
	],
	APIEndpoint::REQ_QUOTA         => TRUE,
	APIEndpoint::REQ_AUTH          => TRUE,
	APIEndpoint::ALLOW_COOKIE_AUTH => TRUE
]);

if (
	!check_perm(
		'grp:admin|grp:editor|grp:display;',
		$SLIDE_GET_ASSET->get_caller()
	)
) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized"
	);
}

$slide = new Slide();
$slide->load($SLIDE_GET_ASSET->get('id'));

$asset = $slide->get_uploaded_asset($SLIDE_GET_ASSET->get('name'));
if ($asset === NULL) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		'No such asset.'
	);
}

header('Content-Type: '.$asset->get_mime());
header('Content-Length: '.filesize($asset->get_fullpath()));
$SLIDE_GET_ASSET->resp_set(fopen($asset->get_fullpath(), 'rb'));
$SLIDE_GET_ASSET->send();
