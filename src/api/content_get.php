<?php
	/*
	*  API handle to get the JSON encoded data of a screen.
	*
	*  GET parameters:
	*    * id = The id of the screen to get.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * id    = The ID of the screen. **
	*      * index = The index of the screen. **
	*      * time  = The time the screen is shown. **
	*      * html  = The HTML content of the screen. **
	*      * error = An error code or 0 on success.
	*
	*    ** (Only exists if the API call was successful.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/api/content_util.php');

	header_plaintext();
	$content_list = get_content_uri_list();

	if (!empty($_GET['id']) && in_array($_GET['id'], $content_list)) {
		$conf = @json_decode(file_get_contents(LIBRESIGNAGE_ROOT.
					CONTENT_DIR.'/'.$_GET['id'].
					'/conf.json'), $assoc=true);
		$html = @file_get_contents(LIBRESIGNAGE_ROOT.
				CONTENT_DIR.'/'.$_GET['id'].
				'/content.html');

		if ($conf === FALSE || $html === FALSE) {
			error_and_exit(1);
		}

		$conf['id'] = $_GET['id'];
		$conf['html'] = $html;
		$conf['error'] = 0;
		echo json_encode($conf);
		exit(0);
	}
	error_and_exit(1);
