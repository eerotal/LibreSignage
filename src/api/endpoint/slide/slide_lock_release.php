<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  ====>
*
*  Attempt to lock a slide.
*
*  This endpoint succeeds if:
*    * The caller is in the 'admin' or 'editor' groups.
*    * The slide has previously been locked by the caller.
*
*  The 'error' value returned by this endpoint is
*    * API_E_OK on success.
*    * API_E_LOCK if the slide is locked by another user.
*
*  POST JSON parameters
*    * id = The ID of the slide to lock.
*
*  Return value
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_LOCK = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'id' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($SLIDE_LOCK);

if (!check_perm('grp:admin|grp:editor;', $SLIDE_LOCK->get_caller())) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized"
	);
}

$slide = new Slide();
$slide->load($SLIDE_LOCK->get('id'));

try {
	$slide->lock_release($SLIDE_LOCK->get_caller()->get_name());
} catch (SlideLockException $e) {
	throw new APIException(
		API_E_LOCK,
		"Failed to lock slide.",
		0,
		$e
	);
}
$slide->write();

$SLIDE_LOCK->resp_set([]);
$SLIDE_LOCK->send();
