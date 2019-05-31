<?php
/*
*  ====>
*
*  Get LibreSignage version information.
*
*  **Request:** GET
*
*  Return value
*    * main    = The LibreSignage version string.
*    * api     = The API version number.
*    * error   = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

APIEndpoint::GET(
	[],
	function($req, $resp) {
		$resp->headers->set('Content-Type', 'application/json');
		$resp->setContent(APIEndpoint::json_encode([
			'ls' => LS_VER,
			'api' => API_VER
		]));
	}
);
