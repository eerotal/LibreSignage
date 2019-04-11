<?php
/*
*  ====>
*
*  Duplicate a slide. The owner of the new slide is the caller
*  of this API endpoint. The new slide is also automatically
*  locked for the caller.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * id = The ID of the slide to duplicate.
*
*  Return value
*    * slide = Duplicated slide data. See slide_get.php for more info.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');

$SLIDE_DUP = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY => array(
		'id' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));

if (!check_perm('grp:admin|grp:editor;', $SLIDE_DUP->get_caller())) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized"
	);
}

$slide = new Slide();
$slide->load($SLIDE_DUP->get('id'));

$new_slide = $slide->dup();
$new_slide->set_owner($SLIDE_DUP->get_caller()->get_name());
$new_slide->lock_acquire($SLIDE_DUP->get_session());

$new_slide->write();


// Juggle slide indices to make sure they are correct.
$queue = $new_slide->get_queue();
$queue->juggle($new_slide->get_id());
$queue->write();

$SLIDE_DUP->resp_set(['slide' => $new_slide->export(FALSE, FALSE)]);
$SLIDE_DUP->send();
