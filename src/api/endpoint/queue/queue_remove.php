<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  ====>
*
*  *Remove a slide queue and all slides in it.*
*
*  GET parameters
*    * name = Queue name.
*
*  Return value
*    * error  = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/queue.php');

$QUEUE_REMOVE = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'name' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($QUEUE_REMOVE);

$tmp = preg_match('/[^a-zA-Z0-9_-]/', $QUEUE_REMOVE->get('name'));
if ($tmp) {
	throw new ArgException(
		"Invalid chars in queue name."
	);
} else if ($tmp === NULL) {
	throw new IntException(
		"Regex match failed."
	);
}

$queue = new Queue($QUEUE_REMOVE->get('name'));
$queue->load();

$caller = $QUEUE_REMOVE->get_caller();

$ALLOW = FALSE;

// Allow admins to remove the queue.
$ALLOW |= $caller->is_in_group('admin');

/*
*  Allow users in the group editor that own the queue and
*  all the slides in it to remove the queue.
*/
$ALLOW |= $caller->is_in_group('editor') &&
	$caller->get_name() == $queue->get_owner() &&
	array_check($queue->slides(), function($s) use($caller) {
		return $s->get_owner() == $caller->get_name();
	});

if (!$ALLOW) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized."
	);
}

$queue->remove();
$QUEUE_REMOVE->send();
