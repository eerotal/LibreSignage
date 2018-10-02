<?php
/*
*  ====>
*
*  *Duplicate a slide. The owner of the new slide is the caller
*   of this API endpoint. The new slide is also automatically
*   locked for the caller.*
*
*  POST JSON parameters
*    * id = The ID of the slide to duplicate.
*
*  Return value
*    * slide
*      * name       = The name of the slide.
*      * index      = The index of the slide.
*      * time       = The amount of time the slide is shown.
*      * owner      = The owner of the slide.
*      * enabled    = Whether the slide is enabled or not.
*      * sched      = Whether slide scheduling is enabled or not.
*      * sched_t_s  = Slide scheduling start timestamp.
*      * sched_t_e  = Slide scheduling end timestamp.
*      * animation  = The slide transition animation.
*      * queue_name = The name of the queue the slide is in.
*    * error        = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_DUP = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'id' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($SLIDE_DUP);

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
