<?php
/*
*  ====>
*
*  Get the messages corresponding to different API error codes.
*  This endpoint doesn't require or consume the API rate quota.
*
*  **Request:** GET
*
*  Return value
*    * messages = A dictionary of error messages.
*    * error    = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

APIEndpoint::GET(
	[],
	function($req, $resp) {
		$resp->headers->set('Content-Type', 'application/json');
		$resp->setContent(APIEndpoint::json_encode(['messages' => API_E_MSG]));
	}
);
