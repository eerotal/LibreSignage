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
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

	$SLIDE_RM = new APIEndpoint(
		$method = API_METHOD['POST'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'id' => API_P_STR
		)
	);
	api_endpoint_init($SLIDE_RM);
	session_start();
	auth_init();

	$slide = new Slide();
	if (!$slide->load($SLIDE_RM->get('id'))) {
		// Slide doesn't exist.
		api_throw(API_E_INVALID_REQUEST);
	}

	$user = auth_session_user();
	$user_quota = new UserQuota($user);

	// Allow admins to remove all slides.
	$flag_auth = auth_is_authorized(
		$groups = array('admin'),
		$users = NULL,
		$redir = FALSE,
		$both = FALSE
	);

	// Allow owner to remove a slide.
	$flag_auth = $flag_auth || auth_is_authorized(
		$groups = array('editor'),
		$users = array($slide->get('owner')),
		$redir = FALSE,
		$both = TRUE
	);

	if (!$flag_auth) {
		api_throw(API_E_NOT_AUTHORIZED);
	}

	// Remove slide and free quota.
	try {
		$slide->remove();
		$user_quota->free_quota('slides');
		$user_quota->flush();
	} catch (Exception $e) {
		api_throw(API_E_INTERNAL, $e);
	}
	$SLIDE_RM->send();
