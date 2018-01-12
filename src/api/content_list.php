<?php
	/*
	*
	*  API handle to get a list of all the existing screens.
	*
	*  Return value:
	*    A JSON encoded array with all the existing screen IDs.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/api/content_util.php');

	header_plaintext();
	echo json_encode(get_content_uri_list());
