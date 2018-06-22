<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  ====>
*
*  *Save a slide.*
*
*  POST JSON parameters
*    * id        = The ID of the slide to modify or either
*      undefined or null for new slide.
*    * name      = The name of the slide.
*    * index     = The index of the slide.
*    * time      = The amount of time the slide is shown.
*    * markup    = The markup of the slide.
*    * enabled   = Whether the slide is enabled or not.
*    * sched     = Whether the slide is scheduled or not.
*    * sched_t_s = The slide schedule starting timestamp.
*    * sched_t_e = The slide schedule ending timestamp.
*    * animation = The slide animation identifier.
*
*  Return value
*    This endpoint returns all the parameters above as well as
*    two additional parameters:
*
*    * owner   = The owner of the slide.
*    * error   = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide.php');

$SLIDE_SAVE = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'id' => API_P_STR|API_P_NULL|API_P_OPT,
		'name' => API_P_STR,
		'index' => API_P_INT,
		'markup' => API_P_STR|API_P_EMPTY_STR_OK,
		'owner' => API_P_UNUSED,
		'time' => API_P_INT,
		'enabled' => API_P_BOOL,
		'sched' => API_P_BOOL,
		'sched_t_s' => API_P_INT,
		'sched_t_e' => API_P_INT,
		'animation' => API_P_INT
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($SLIDE_SAVE);

$flag_new_slide = FALSE;
$flag_auth = FALSE;

$user = $SLIDE_SAVE->get_caller();
$quota = new UserQuota($user);
$slide = new Slide();

if ($SLIDE_SAVE->has('id', TRUE)) {
	if ($slide->load($SLIDE_SAVE->get('id'))) {
		// Allow admins to modify slides.
		$flag_auth = $user->is_in_group('admin');

		// Allow owner to modify slide.
		$flag_auth |= ($user->is_in_group('editor') &&
			$user->get_name() === $slide->get_owner());
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
} else {
	/*
	*  Allow users in the editor and admin groups
	*  to create slides.
	*/
	if (!$user->is_in_group('editor') &&
		!$user->is_in_group('admin')) {
		throw new APIException(
			API_E_NOT_AUTHORIZED,
			"Not authorized."
		);
	}

	// Set the current user as the owner.
	$slide->gen_id();
	$slide->set_owner($user->get_name());
	$flag_new_slide = TRUE;
}

try {
	$slide->set_name($SLIDE_SAVE->get('name'));
	$slide->set_index($SLIDE_SAVE->get('index'));
	$slide->set_time($SLIDE_SAVE->get('time'));
	$slide->set_markup($SLIDE_SAVE->get('markup'));
	$slide->set_enabled($SLIDE_SAVE->get('enabled'));
	$slide->set_sched($SLIDE_SAVE->get('sched'));
	$slide->set_sched_t_s($SLIDE_SAVE->get('sched_t_s'));
	$slide->set_sched_t_e($SLIDE_SAVE->get('sched_t_e'));
	$slide->set_animation($SLIDE_SAVE->get('animation'));
} catch (ArgException $e) {
	/*
	*  Throw an API_E_INVALID_REQUEST exception if
	*  the Slide::set_* functions throw ArgExceptions.
	*/
	throw new APIException(
		API_E_INVALID_REQUEST,
		$e->getMessage()
	);
}

if ($flag_new_slide) {
	if (!$quota->use_quota('slides')) {
		throw new APIException(
			API_E_QUOTA_EXCEEDED,
			"Slide quota exceeded."
		);
	}
	$quota->flush();
}

$slide->write();
juggle_slide_indices($slide->get_id());

$SLIDE_SAVE->resp_set($slide->get_data_array());
$SLIDE_SAVE->send();
