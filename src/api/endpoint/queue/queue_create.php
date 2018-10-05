<?php
/*
*  ====>
*
*  *Create a slide queue.*
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
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/queue.php');

$QUEUE_CREATE = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY => array(
		'name' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($QUEUE_CREATE);

if (!check_perm('grp:admin|grp:editor;', $QUEUE_CREATE->get_caller())) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		'Not authorized.'
	);
}

$tmp = preg_match('/[^a-zA-Z0-9_-]/', $QUEUE_CREATE->get('name'));
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

if (queue_exists($QUEUE_CREATE->get('name'))) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		'Queue already exists.'
	);
}

$queue = new Queue($QUEUE_CREATE->get('name'));
$queue->set_owner($QUEUE_CREATE->get_caller()->get_name());
$queue->write();

$QUEUE_CREATE->send();
