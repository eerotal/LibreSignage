<?php
/*
*  ====>
*
*  *Get a list of all the existing slides.*
*
*  Return value
*    * An array with all the existing slide IDs.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_LIST = new APIEndpoint(array(
	APIEndpoint::METHOD 		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE 	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT_URL		=> array(),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($SLIDE_LIST);

$SLIDE_LIST->resp_set(array(
	'slides' => slides_id_list()
));
$SLIDE_LIST->send();

