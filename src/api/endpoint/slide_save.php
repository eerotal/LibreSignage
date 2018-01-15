<?php
	/*
	*
	*  API handle to create a new slide.
	*
	*  POST parameters:
	*    * id    = The ID of the slide.
	*    * index = The index of the slide.
	*    * time  = The amount of time the slide is shown.
	*    * markup  = The markup of the slide.
	*
	*  Return value:
	*    A JSON encoded array with the following keys:
	*     * id    = The ID of the created slide. **
	*     * index = The index of the created slide. **
	*     * time  = The amount of time the slide is shown. **
	*     * error = An error code or API_E_OK on success. ***
	*
	*   **  (Only exists when the call was successful.)
	*   *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_errors.php');

	header_plaintext();


	if (!array_is_subset(SLIDE_REQ_KEYS, array_keys($_POST))) {
		// Required params do not exist. Return error.
		error_and_exit(API_E_INVALID_REQUEST);
	}


	$params_sanitized = array();
	$opt_index = array(
		'options' => array(
			'min_range' => 0
		)
	);

	// Sanitize 'id'.
	$tmp = basename($_POST['id']);
	$params_sanitized['id'] = $tmp;

	// Sanitize 'index'.
	$tmp = filter_var($_POST['index'], FILTER_VALIDATE_INT,
				$opt_index);
	if ($tmp === FALSE) {
		error_and_exit(API_E_INVALID_REQUEST);
	}
	$params_sanitized['index'] = $tmp;

	// Sanitize 'time'.
	$tmp = filter_var($_POST['time'], FILTER_VALIDATE_FLOAT);
	if ($tmp === FALSE) {
		error_and_exit(API_E_INVALID_REQUEST);
	}
	$params_sanitized['time'] = $tmp;

	// TODO: Sanitize & process the markup!
	$params_sanitized['markup'] = $_POST['markup'];

	$slide = new Slide();
	if (!$slide->set($params_sanitized['id'],
			$params_sanitized)) {
		error_and_exit(API_E_INTERNAL);
	}

	try {
		$slide->write();
	} catch (Exception $e) {
		error_and_exit(API_E_INTERNAL);
	}

	$ret = $slide->get_data();
	$ret['error'] = API_E_OK;
	$ret_str = json_encode($ret);
	if ($ret_str === FALSE) {
		error_and_exit(API_E_INTERNAL);
	}
	echo $ret_str;
