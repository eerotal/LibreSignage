<?php
	/*
	*
	*  API handle to get a list of all the existing slides.
	*
	*  Return value:
	*    A JSON encoded array with all the existing slide IDs.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

	header_plaintext();
	echo json_encode(get_slides_id_list());
