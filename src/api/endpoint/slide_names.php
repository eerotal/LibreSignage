<?php
	/*
	*
	*  API handle to get a dictionary of all existing slide names.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following keys:
	*      * names = The names dictionary. Contains id-name pairs. **
	*      * error = An error code or API_E_OK on success. ***
	*
	*    **  (Only exists if the call was successful.)
	*    *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_errors.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

	header_plaintext();

	$slides = get_slides_id_list();
	$ret = array('names' => array());
	$tmp_slide = new Slide();

	foreach($slides as $s) {
		$tmp_slide->clear();
		try {
			$tmp_slide->load($s);
		} catch (Exception $e) {
			error_and_exit(API_E_INTERNAL);
		}
		$ret['names'][$s] = $tmp_slide->get('name');
	}
	$ret['error'] = API_E_OK;

	$ret_json = json_encode($ret);
	if ($ret === FALSE) {
		error_and_exit(API_E_INTERNAL);
	}
	echo $ret_json;

