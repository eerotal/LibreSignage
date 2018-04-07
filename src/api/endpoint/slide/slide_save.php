<?php
/*
*  ====>
*
*  *Save a slide.*
*
*  POST JSON parameters
*    * id      = The ID of the slide to modify or either
*      undefined or null for new slide.
*    * name    = The name of the slide.
*    * index   = The index of the slide.
*    * time    = The amount of time the slide is shown.
*    * markup  = The markup of the slide.
*
*  Return value
*    * id     = The ID of the slide.
*    * name   = The name of the slide.
*    * index  = The index of the created slide.
*    * time   = The amount of time the slide is shown.
*    * owner  = The owner of the slide.
*    * error  = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');

$SLIDE_SAVE = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'id' => API_P_STR|API_P_NULL|API_P_OPT,
		'name' => API_P_STR,
		'index' => API_P_INT,
		'markup' => API_P_STR|API_P_EMPTY_STR_OK,
		'owner' => API_P_UNUSED,
		'time' => API_P_INT
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_API_KEY	=> TRUE
));
api_endpoint_init($SLIDE_SAVE);

$flag_new_slide = FALSE;
$flag_auth = FALSE;
$slide_data = array();

$user = $SLIDE_SAVE->get_caller();
$user_quota = new UserQuota($user);
$slide = new Slide();

if ($SLIDE_SAVE->has('id', TRUE)) {
	if ($slide->load($SLIDE_SAVE->get('id'))) {
		// Allow admins to modify slides.
		$flag_auth = $user->is_in_group('admin');

		// Allow owner to modify slide.
		$flag_auth |= ($user->is_in_group('editor') &&
			$user->get_name() === $slide->get('owner')));
		if (!$flag_auth) {
			throw new APIException(
				API_E_NOT_AUTHORIZED,
				"Not authorized."
			);
		}
	} else {
		// Slide with the supplied ID doesn't exist.
		throw new APIException(
			API_E_INVALID_REQUEST,
			"No such slide."
		);
	}
	// Initially load the existing slide data.
	$slide_data = $slide->get_data();
	$flag_new_slide = FALSE;
} else {
	/*
	*  Allow users in the editor and
	*  admin groups to create slides.
	*/
	if (!$user->is_in_group('editor') &&
		!$user->is_in_group('admin')) {
		throw new APIException(
			API_E_NOT_AUTHORIZED,
			"Not authorized."
		);
	}

	// Set current user as the owner.
	$slide_data['owner'] = $user->get_name();
	$flag_new_slide = TRUE;
}

// Only allow alphanumeric characters in the name.
$tmp = preg_match('/[^a-zA-Z0-9_-]/',
		$SLIDE_SAVE->get('name'));
if ($tmp) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Invalid chars in slide name."
	);
} else if ($tmp === NULL) {
	throw new APIException(
		API_E_INTERNAL,
		"preg_match() failed."
	);
}
if (strlen($SLIDE_SAVE->get('name')) >
		gtlim('SLIDE_NAME_MAX_LEN')) {
	throw new APIException(
		API_E_LIMITED,
		"Slide name too long."
	);
}
$slide_data['name'] = $SLIDE_SAVE->get('name');

// Make sure 0 <= index <= SLIDE_MAX_INDEX.
if ($SLIDE_SAVE->get('index') < 0 ||
	$SLIDE_SAVE->get('index') > gtlim('SLIDE_MAX_INDEX')) {
	throw new APIException(
		API_E_LIMITED,
		"Invalid slide index."
	);
}
$slide_data['index'] = $SLIDE_SAVE->get('index');

// Make sure SLIDE_MIN_TIME <= time <= SLIDE_MAX_TIME.
if ($SLIDE_SAVE->get('time') < gtlim('SLIDE_MIN_TIME') ||
	$SLIDE_SAVE->get('time') > gtlim('SLIDE_MAX_TIME')) {
	throw new APIException(
		API_E_LIMITED,
		"Invalid slide time."
	);
}
$slide_data['time'] = $SLIDE_SAVE->get('time');

// Make sure the size of the markup is not too big.
if (strlen($SLIDE_SAVE->get('markup')) >
		gtlim('SLIDE_MARKUP_MAX_LEN')) {
	throw new APIException(
		API_E_LIMITED,
		"Slide markup too long."
	);
}
$slide_data['markup'] = $SLIDE_SAVE->get('markup');

if (!$slide->set_data($slide_data)) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Failed to set slide data."
	);
}

if ($flag_new_slide) {
	if (!$user_quota->use_quota('slides')) {
		throw new APIException(
			API_E_QUOTA_EXCEEDED,
			"Slide quota exceeded."
		);
	}
	$user_quota->flush();
}
$slide->write();
juggle_slide_indices($slide->get('id'));

$SLIDE_SAVE->resp_set($slide->get_data());
$SLIDE_SAVE->send();
