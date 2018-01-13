<?php
	/*
	*  API handle to get the JSON encoded data of a slide.
	*
	*  GET parameters:
	*    * id = The id of the slide to get.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * id      = The ID of the slide. **
	*      * index   = The index of the slide. **
	*      * time    = The time the slide is shown. **
	*      * markup  = The markup of the slide. **
	*      * error   = An error code or API_E_OK on success. ***
	*
	*    **  (Only exists if the API call was successful.)
	*    *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_errors.php');

	header_plaintext();
	$list = get_slides_id_list();

	if (!empty($_GET['id']) && in_array($_GET['id'], $list)) {
		$slide = new Slide();
		try {
			$slide->load($_GET['id']);
		} catch (Exception $e) {
			error_and_exit(API_E_INTERNAL);
		}

		$ret = $slide->get_data();
		$ret['error'] = 0;
		$ret_str =  json_encode($ret);
		if ($ret_str === FALSE) {
			error_and_exit(API_E_INTERNAL);
		}
		echo $ret_str;
		exit(0);
	}
	error_and_exit(API_E_INVALID_REQUEST);
