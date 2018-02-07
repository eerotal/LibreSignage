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

	$flag_new_slide = FALSE;
	$flag_auth = FALSE;
	$slide_data = array();

	$user = auth_session_user();
	$user_quota = new UserQuota($user);
	$slide = new Slide();

	if ($SLIDE_SAVE->has('id', TRUE)) {
		if ($slide->load($SLIDE_SAVE->get('id'))) {
			// Allow admins to modify slides.
			$flag_auth = auth_is_authorized(
				$groups = array('admin'),
				$users = NULL,
				$redir = FALSE,
				$both = FALSE
			);
			// Allow owner to modify slide.
			$flag_auth = ($flag_auth || auth_is_authorized(
				$groups = array('editor'),
				$users = array($slide->get('owner')),
				$redir = FALSE,
				$both = TRUE
			));
			if (!$flag_auth) {
				api_throw(API_E_NOT_AUTHORIZED);
			}
		} else {
			// Slide with the supplied ID doesn't exist.
			api_throw(API_E_INVALID_REQUEST);
		}
		// Initially load the existing slide data.
		$slide_data = $slide->get_data();
		$flag_new_slide = FALSE;
	} else {
		/*
		*  Allow users in the editor and
		*  admin groups to create slides.
		*/
		$flag_auth = auth_is_authorized(
			$groups = array('editor', 'admin'),
			$users = NULL,
			$redir = FALSE,
			$both = FALSE
		);
		if (!$flag_auth) {
			api_throw(API_E_NOT_AUTHORIZED);
		}

		// Set current user as the owner.
		$slide_data['owner'] = $user->get_name();
		$flag_new_slide = TRUE;
	}

	// Only allow alphanumeric characters in the name.
	$tmp = preg_match('/[^a-zA-Z0-9_-]/',
			$SLIDE_SAVE->get('name'));
	if ($tmp) {
		api_throw(API_E_INVALID_REQUEST);
	} else if ($tmp === NULL) {
		api_throw(API_E_INTERNAL);
	}
	$slide_data['name'] = $SLIDE_SAVE->get('name');

	// Make sure index >= 0.
	if ($SLIDE_SAVE->get('index') < 0) {
		api_throw(API_E_INVALID_REQUEST);
	}
	$slide_data['index'] = $SLIDE_SAVE->get('index');

	// Make sure SLIDE_MIN_TIME <= time <= SLIDE_MAX_TIME.
	if ($SLIDE_SAVE->get('time') < SLIDE_MIN_TIME ||
		$SLIDE_SAVE->get('time') > SLIDE_MAX_TIME) {
		api_throw(API_E_INVALID_REQUEST);
	}
	$slide_data['time'] = $SLIDE_SAVE->get('time');

	// Make sure the size of the markup is not too big.
	if (strlen($SLIDE_SAVE->get('markup')) > SLIDE_MAX_MARKUP_SIZE) {
		api_throw(API_E_INVALID_REQUEST);
	}
	$slide_data['markup'] = $SLIDE_SAVE->get('markup');

	if (!$slide->set_data($slide_data)) {
		api_throw(API_E_INVALID_REQUEST);
	}

	try {
		if ($flag_new_slide) {
			if (!$user_quota->use_quota('slides')) {
				api_throw(API_E_QUOTA_EXCEEDED);
			}
			$user_quota->flush();
		}
		$slide->write();
		juggle_slide_indices($slide->get('id'));
	} catch (Exception $e) {
		api_throw(API_E_INTERNAL, $e);
	}

	$SLIDE_SAVE->resp_set($slide->get_data());
	$SLIDE_SAVE->send();
