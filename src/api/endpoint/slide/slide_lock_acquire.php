<?php
/*
*  ====>
*
*  Attempt to lock a slide.
*
*  This endpoint succeeds if:
*    * The caller is in the 'admin' or 'editor' groups.
*    * The slide is not already locked by another user.
*    * The user has modification permissions for the slide.
*
*  The 'error' value returned by this endpoint is
*    * API_E_OK if the slide locking succeeds.
*    * API_E_LOCK if the slide is already locked by another user.
*
*  POST JSON parameters
*    * id = The ID of the slide to lock.
*
*  Return value
*    * expire = The lock expiration timestamp.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_LOCK_ACQUIRE = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'id' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($SLIDE_LOCK_ACQUIRE);

if (
	!check_perm(
		'grp:admin|grp:editor;',
		$SLIDE_LOCK_ACQUIRE->get_caller()
	)
) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized"
	);
}

$slide = new Slide();
$slide->load($SLIDE_LOCK_ACQUIRE->get('id'));
try {
	$slide->lock_acquire($SLIDE_LOCK_ACQUIRE->get_session());
} catch (SlideLockException $e) {
	throw new APIException(
		API_E_LOCK,
		"Failed to lock slide.",
		0,
		$e
	);
}
$slide->write();

$SLIDE_LOCK_ACQUIRE->resp_set([
	'expire' => $slide->get_lock()->get_expire(),
	'error' => API_E_OK
]);
$SLIDE_LOCK_ACQUIRE->send();
