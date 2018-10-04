<?php
/*
*  ====>
*
*  *Get a list of the existing slide queue names*
*
*  Return value
*    * queues = A list containing the slide queue names.
*    * error  = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/queue.php');

$QUEUE_LIST = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT_URL => array(),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($QUEUE_LIST);

$QUEUE_LIST->resp_set(array('queues' => queue_list()));
$QUEUE_LIST->send();
