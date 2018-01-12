<?php
	/*
	*  An API handle to remove an existing screen.
	*
	*  POST parameters:
	*    * id = The id of the screen to remove.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * error = An error code or 0 on success.
	*/
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/content_util.php');

	header_plaintext();
	$content_list = get_content_uri_list();

	if (!empty($_POST['id']) && in_array($_POST['id'], $content_list)) {
		if (!rmdir_recursive(LIBRESIGNAGE_ROOT.CONTENT_DIR
				.'/'.$_POST['id'])) {
			error_and_exit(1);
		}
		echo json_encode(array("error" => 0));
		exit(0);
	}
	error_and_exit(2);
