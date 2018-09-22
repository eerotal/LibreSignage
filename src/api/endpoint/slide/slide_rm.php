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
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

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
$slide->load($SLIDE_RM->get('id'));

// Get the slide owner's quota for freeing some of it.
$owner = new User($slide->get_owner());
$owner_quota = new UserQuota($owner);

// Allow admins to remove all slides.
$ALLOW = FALSE;
$ALLOW |= check_perm(
	'grp:admin;',
	$SLIDE_RM->get_caller()
);
$ALLOW |= check_perm(
	'grp:editor&usr:'.$slide->get_owner().';',
	$SLIDE_RM->get_caller()
);
if (!$ALLOW) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized."
	);
}

$slide->remove();

// Normalize slide indices now that one is left unused.
$queue = new Queue($slide->get_queue_name());
$queue->load();
$queue->normalize();

$owner_quota->free_quota('slides');
$owner_quota->flush();

$SLIDE_RM->send();
