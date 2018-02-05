<?php
	/*
	*
	*  API handle to create a new slide.
	*
	*  POST JSON parameters:
	*    * id      = The ID of the slide to modify or
	*                __API_K_NULL__ for new slide.
	*    * name    = The name of the slide.
	*    * index   = The index of the slide.
	*    * time    = The amount of time the slide is shown.
	*    * markup  = The markup of the slide.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following keys:
	*     * id     = The ID of the created slide. **
	*     * name   = The name of the slide. **
	*     * index  = The index of the created slide. **
	*     * time   = The amount of time the slide is shown. **
	*     * error  = An error code or API_E_OK on success. ***
	*
	*   **  (Only exists if the call was successful.)
	*   *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');

	$SLIDE_SAVE = new APIEndpoint(
		$method = API_METHOD['POST'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'id' => API_P_STR|API_P_OPT|API_P_NULL,
			'name' => API_P_STR,
			'index' => API_P_STR,
			'markup' => API_P_STR,
			'time' => API_P_INT
		)
	);
	api_endpoint_init($SLIDE_SAVE);

	$params_sanitized = array();
	$opt_index = array(
		'options' => array(
			'min_range' => 0
		)
	);

	// Only allow alphanumeric characters in the 'name'.
	$tmp = preg_replace('/[^a-zA-Z0-9_-]/', '',
			$SLIDE_SAVE->get('name'));
	if ($tmp === NULL) {
		api_throw(API_E_INTERNAL);
	}
	$params_sanitized['name'] = $tmp;

	// Make sure 'index' is an integer value in the correct range.
	$tmp = filter_var($SLIDE_SAVE->get('index'),
			FILTER_VALIDATE_INT, $opt_index);
	if ($tmp === FALSE) {
		api_throw(API_E_INVALID_REQUEST);
	}
	$params_sanitized['index'] = $tmp;

	// Make sure 'time' is a float value.
	$tmp = filter_var($SLIDE_SAVE->get('time'),
				FILTER_VALIDATE_FLOAT);
	if ($tmp === FALSE) {
		api_throw(API_E_INVALID_REQUEST);
	}
	$params_sanitized['time'] = $tmp;

	$params_sanitized['markup'] = $SLIDE_SAVE->get('markup');

	/*
	*  If a slide ID is supplied *attempt* to use it.
	*  The $slide->set_data() function will do further
	*  checks on whether the ID is actually valid.
	*/
	if ($SLIDE_SAVE->has('id', TRUE)) {
		$params_sanitized['id'] = $SLIDE_SAVE->get('id');
	}

	$slide = new Slide();
	if (!$slide->set_data($params_sanitized)) {
		/*
		*  Fails on missing parameters or if the
		*  provided ID doesn't exist.
		*/
		api_throw(API_E_INVALID_REQUEST);
	}

	try {
		$slide->write();
	} catch (Exception $e) {
		api_throw(API_E_INTERNAL, $e);
	}

	juggle_slide_indices($slide->get('id'));

	$SLIDE_SAVE->resp_set($slide->get_data());
	$SLIDE_SAVE->send();
