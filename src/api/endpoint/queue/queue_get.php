<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  ====>
*
*  *Get a slide queue.*
*
*  GET parameters
*    * name = The name of the queue to get.
*
*  Return value
*    * slides = A list containing the IDs of the slides in the queue.
*    * error  = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/queue.php');

$QUEUE_GET = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'name' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($QUEUE_GET);

$tmp = preg_match('/[^a-zA-Z0-9_-]/', $QUEUE_GET->get('name'));
if ($tmp) {
	throw new ArgException(
		"Invalid chars in queue name."
	);
} else if ($tmp === NULL) {
	throw new IntException(
		"Regex match failed."
	);
}

$queue = new Queue($QUEUE_GET->get('name'));
$queue->load();

$slides = $queue->slides();
$ret = array();
foreach ($slides as $s) {
	$ret[$s->get_id()] = $s->get_data_array();
}
$QUEUE_GET->resp_set(array('slides' => $ret));
$QUEUE_GET->send();
