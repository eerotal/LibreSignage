<?php
	/*
	*  ====>
	*
	*  *Get a list of all the existing slides.*
	*
	*  Return value
	*    * An array with all the existing slide IDs.
	*
	*  <====
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

	$SLIDE_LIST = new APIEndpoint(
		$method = API_METHOD['GET'],
		$response_type = API_RESPONSE['JSON'],
		$format = NULL
	);
	session_start();
	api_endpoint_init($SLIDE_LIST, auth_session_user());

	$SLIDE_LIST->resp_set(array(
		'slides' => get_slides_id_list()
	));
	$SLIDE_LIST->send();
