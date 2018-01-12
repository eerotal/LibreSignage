<?php
	/*
	*
	*  API handle to create a new screen.
	*
	*  POST parameters:
	*    * id    = The ID of the screen.
	*    * index = The index of the screen.
	*    * time  = The amount of time the screen is shown.
	*    * html  = The HTML content of the screen.
	*
	*  Return value:
	*    A JSON encoded array with the following keys:
	*     * id    = The ID of the created screen. **
	*     * index = The index of the created screen. **
	*     * time  = The amount of time the screen is shown. **
	*     * error = An error code or 0 on success.
	*
	*   ** (Only exists when the call was successful.)
	*
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/content_util.php');

	header_plaintext();

	define('REQ_PARAMS', array(
		'id',
		'index',
		'time',
		'html'
	));

	if (!array_is_subset(REQ_PARAMS, array_keys($_POST))) {
		// Required params do not exist. Return error.
		error_and_exit(1);
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
	$tmp = filter_var($_POST['index'], FILTER_VALIDATE_INT, $opt_index);
	if ($tmp === FALSE) {
		error_and_exit(2);
	}
	$params_sanitized['index'] = $tmp;

	// Sanitize 'time'.
	$tmp = filter_var($_POST['time'], FILTER_VALIDATE_FLOAT);
	if ($tmp === FALSE) {
		error_and_exit(3);
	}
	$params_sanitized['time'] = $tmp;

	$SCREEN_PATH = LIBRESIGNAGE_ROOT.CONTENT_DIR.'/'.
			$params_sanitized['id'];
	$HTML_PATH = $SCREEN_PATH.'/content.html';
	$CONF_PATH = $SCREEN_PATH.'/conf.json';

	// Write data to files.
	// TODO: SANITIZE THE HTML INPUT!
	if (!file_exists($SCREEN_PATH) || !is_dir($SCREEN_PATH)) {
		if (!@mkdir($SCREEN_PATH, 0775, true)) {
			error_and_exit(4);
		}
	}
	$ret = file_put_contents($HTML_PATH, $_POST['html']);
	if ($ret === FALSE) {
		error_and_exit(5);
	}
	$ret = file_put_contents($CONF_PATH, json_encode($params_sanitized));
	if ($ret === FALSE) {
		error_and_exit(6);
	}

	$params_sanitized['error'] = 0;
	echo json_encode($params_sanitized);
	exit(0);
