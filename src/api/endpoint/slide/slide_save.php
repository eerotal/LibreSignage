<?php
	/*
	*
	*  API handle to create a new slide.
	*
	*  POST JSON parameters:
	*    * id      = The ID of the slide to modify or either
	*                undefined or null for new slide.
	*    * name    = The name of the slide.
	*    * index   = The index of the slide.
	*    * time    = The amount of time the slide is shown.
	*    * markup  = The markup of the slide.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following keys:
	*     * id     = The ID of the slide. **
	*     * name   = The name of the slide. **
	*     * index  = The index of the created slide. **
	*     * time   = The amount of time the slide is shown. **
	*     * owner  = The owner of the slide. **
	*     * error  = An error code or API_E_OK on success. ***
	*
	*   **  (Only exists if the call was successful.)
	*   *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');

	define("SLIDE_MIN_TIME", 1000);
	define("SLIDE_MAX_TIME", 20000);

	$SLIDE_SAVE = new APIEndpoint(
		$method = API_METHOD['POST'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'id' => API_P_STR|API_P_OPT|API_P_NULL,
			'name' => API_P_STR,
			'index' => API_P_INT,
			'markup' => API_P_STR,
			'time' => API_P_INT
		)
	);
	api_endpoint_init($SLIDE_SAVE);
	session_start();
	auth_init();

	$slide_data = array();
	$auth = FALSE;
	$slide = new Slide();

	if ($SLIDE_SAVE->has('id', TRUE)) {
		if ($slide->load($SLIDE_SAVE->get('id'))) {
			// Allow admins to modify slides.
			$auth = auth_is_authorized(
				$groups = array('admin'),
				$users = NULL,
				$redir = FALSE,
				$both = FALSE
			);

			// Allow owner to modify slides.
			$auth = ($auth || auth_is_authorized(
				$groups = array('editor'),
				$users = array($slide->get('owner')),
				$redir = FALSE,
				$both = TRUE
			));
			if (!$auth) {
				api_throw(API_E_NOT_AUTHORIZED);
			}
		} else {
			// Slide with the supplied ID doesn't exist.
			api_throw(API_E_INVALID_REQUEST);
		}
		// Initially load the existing slide data.
		$slide_data = $slide->get_data();
	} else {
		// Allow users in the editor group to create slides.
		$auth = auth_is_authorized(
			$groups = array('editor'),
			$users = NULL,
			$redir = FALSE,
			$both = FALSE
		);
		if (!$auth) {
			api_throw(API_E_NOT_AUTHORIZED);
		}

		// Set current user as the owner.
		$slide_data['owner'] = auth_session_user()->get_name();
	}

	// Only allow alphanumeric characters in the name.
	$tmp = preg_replace('/[^a-zA-Z0-9_-]/', '', $SLIDE_SAVE->get('name'));
	if ($tmp === NULL) {
		api_throw(API_E_INTERNAL);
	}
	$slide_data['name'] = $tmp;

	// Make sure index >= 0.
	if ($SLIDE_SAVE->get('index') < 0) {
		api_throw(API_E_INVALID_REQUEST);
	}
	$slide_data['index'] = $SLIDE_SAVE->get('index');

	// Make sure time > 0.
	if ($SLIDE_SAVE->get('time') < SLIDE_MIN_TIME ||
		$SLIDE_SAVE->get('time') > SLIDE_MAX_TIME) {
		api_throw(API_E_INVALID_REQUEST);
	}
	$slide_data['time'] = $SLIDE_SAVE->get('time');

	$slide_data['markup'] = $SLIDE_SAVE->get('markup');

	if (!$slide->set_data($slide_data)) {
		api_throw(API_E_INVALID_REQUEST);
	}

	try {
		$slide->write();
		juggle_slide_indices($slide->get('id'));
	} catch (Exception $e) {
		api_throw(API_E_INTERNAL, $e);
	}

	$SLIDE_SAVE->resp_set($slide->get_data());
	$SLIDE_SAVE->send();
