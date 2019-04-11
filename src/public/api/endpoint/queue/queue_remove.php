<?php
/*
*  ====>
*
*  Remove a slide queue and all slides in it.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * name = Queue name.
*
*  Return value
*    * error  = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/queue.php');

$QUEUE_REMOVE = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY => array(
		'name' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));

$tmp = preg_match('/[^a-zA-Z0-9_-]/', $QUEUE_REMOVE->get('name'));
if ($tmp) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Invalid chars in queue name."
	);
} else if ($tmp === NULL) {
	throw new APIException(
		API_E_INTERNAL,
		"Regex match failed."
	);
}

$queue = new Queue($QUEUE_REMOVE->get('name'));
$queue->load();

$owner = $queue->get_owner();
$caller = $QUEUE_REMOVE->get_caller();

$ALLOW = FALSE;

// Allow users in the admin group.
$ALLOW = check_perm('grp:admin;', $caller);

/*
*  Allow users in the editor group if they own
*  the queue and all the slides in it.
*/
$ALLOW = $ALLOW || (
		check_perm("grp:editor&usr:$owner;", $caller) &&
		array_check($queue->slides(), function($s) use($caller) {
			return $s->get_owner() == $caller->get_name();
		})
	);

if (!$ALLOW) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized."
	);
}

$queue->remove();
$QUEUE_REMOVE->send();
