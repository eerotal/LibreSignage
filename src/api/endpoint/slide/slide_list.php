<?php
	/*
	*
	*  API handle to get a list of all the existing slides.
	*
	*  Return value:
	*    A JSON encoded array with all the existing slide IDs.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

	$SLIDE_LIST = new APIEndpoint(
		$method = API_METHOD['GET'],
		$response_type = API_RESPONSE['JSON'],
		$format = NULL
	);
	api_endpoint_init($SLIDE_LIST);

	echo json_encode(get_slides_id_list());
