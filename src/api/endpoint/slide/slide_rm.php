<?php
	/*
	*  An API handle to remove an existing slide.
	*
	*  POST parameters:
	*    * id = The id of the slide to remove.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * error = An error code or API_E_OK on success. **
	*
	*    ** (The error codes are listed in api_errors.php.)
	*/
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_errors.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

	header_plaintext();
	$slide_list = get_slides_id_list();

	if (!empty($_POST['id']) &&
		in_array($_POST['id'], $slide_list)) {

		if (!rmdir_recursive(LIBRESIGNAGE_ROOT.SLIDES_DIR
				.'/'.$_POST['id'])) {
			error_and_exit(API_E_INTERNAL);
		}
		echo json_encode(array("error" => API_E_OK));
		exit(0);
	}
	error_and_exit(API_E_INVALID_REQUEST);
