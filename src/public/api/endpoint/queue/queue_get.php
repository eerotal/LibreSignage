<?php
/*
*  ====>
*
*  Get a slide queue.
*
*  **Request:** GET
*
*  Parameters
*    * name = The name of the queue to get.
*
*  Return value
*    * owner  = The owner of the queue.
*    * slides = A list containing the IDs of the slides in the queue.
*    * error  = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/queue.php');

$QUEUE_GET = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::FORMAT_URL => array(
		'name' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));

$tmp = preg_match('/[^a-zA-Z0-9_-]/', $QUEUE_GET->get('name'));
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

$queue = new Queue($QUEUE_GET->get('name'));
$queue->load();

$QUEUE_GET->resp_set($queue->get_data_array());
$QUEUE_GET->send();
