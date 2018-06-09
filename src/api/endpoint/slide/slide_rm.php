<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

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

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide.php');

$SLIDE_RM = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'id' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($SLIDE_RM);

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
	$slide_owner = new User($slide->get_owner());
} catch (ArgException $e){
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Failed to load user.", 0, $e
	);
}
$slide_owner_quota = new UserQuota($slide_owner);

// Allow admins to remove all slides.
$flag_auth = $SLIDE_RM->get_caller()->is_in_group('admin');

// Allow the owner to remove a slide if they are in the editor group.
$flag_auth |= ($SLIDE_RM->get_caller()->is_in_group('editor') &&
	$SLIDE_RM->get_caller()->get_name() === $slide->get_owner());

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
