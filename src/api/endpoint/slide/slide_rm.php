<?php
	/*
	*  ====>
	*
	*  *Remove a slide.*
	*
	*  POST parameters
	*    * id = The id of the slide to remove.
	*
	*  Return value
	*    * error = An error code or API_E_OK on success.
	*
	*  <====
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
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
	session_start();
	api_endpoint_init($SLIDE_RM, auth_session_user());

	$slide = new Slide();
	if (!$slide->load($SLIDE_RM->get('id'))) {
		// Slide doesn't exist.
		throw new APIException(
			API_E_INVALID_REQUEST,
			"Slide doesn't exist."
		);
	}

	// Get the slide owner's quota for freeing some of it.
	try {
		$slide_owner = new User($slide->get('owner'));
	} catch (ArgException $e){
		throw new APIException(
			API_E_INVALID_REQUEST,
			"Failed to load slide.", 0, $e
		);
	}
	$slide_owner_quota = new UserQuota($slide_owner);

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
		throw new APIException(
			API_E_NOT_AUTHORIZED,
			"Not authorized."
		);
	}

	$slide->remove();
	$slide_owner_quota->free_quota('slides');
	$slide_owner_quota->flush();

	$SLIDE_RM->send();
