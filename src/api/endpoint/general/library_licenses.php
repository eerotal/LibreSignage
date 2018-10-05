<?php

/*
*  ====>
*
*  *Get the Markdown version of the third party licenses file.
*  This endpoint doesn't require or consume the API rate quota.*
*
*  <====
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$LIBRARY_LICENSES = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['text/plain'],
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_AUTH		=> FALSE
));
api_endpoint_init($LIBRARY_LICENSES);

$LIBRARY_LICENSES->resp_set(file_get_contents(
	realpath(LIBRESIGNAGE_ROOT.LICENSES_EXT_RST)
));
$LIBRARY_LICENSES->send();
