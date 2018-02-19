<?php
	/*
	*  ====>
	*
	*  Get the Markdown version of the third party licenses file.
	*
	*  <====
	*/
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');

	$LIBRARY_LICENSES = new APIEndpoint(
		$method = API_METHOD['GET'],
		$response_type = API_RESPONSE['TEXT'],
		$format = NULL
	);
	api_endpoint_init($LIBRARY_LICENSES);

	$LIBRARY_LICENSES->resp_set(file_get_contents(
		realpath(LIBRESIGNAGE_ROOT.LICENSES_EXT_MD)
	));
	$LIBRARY_LICENSES->send();
