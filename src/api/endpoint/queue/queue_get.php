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

$queue = (new Queue())->load($QUEUE_GET->get('name'));
$QUEUE_GET->resp_set(
	array(
		'slides' => array_map(
			function($s) {
				return $s->get_id();
			},
			$queue->slides()
		)
	)
);
$QUEUE_GET->send();
