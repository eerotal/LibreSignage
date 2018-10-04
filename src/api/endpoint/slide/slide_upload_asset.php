<?php
/*
*  ====>
*
*
*
*  Return value
*    * error        = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_UPLOAD_ASSET = new APIEndpoint([
	APIEndpoint::METHOD         => API_METHOD['POST'],
	APIEndpoint::REQUEST_TYPE   => API_REQUEST['MEDIA'],
	APIEndpoint::RESPONSE_TYPE  => API_RESPONSE['JSON'],
	APIEndpoint::FORMAT_URL => [
		'id' => API_P_STR
	],
	APIEndpoint::REQ_QUOTA      => TRUE,
	APIEndpoint::REQ_AUTH       => TRUE
]);
api_endpoint_init($SLIDE_UPLOAD_ASSET);

$SLIDE_UPLOAD_ASSET->send();
