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

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

$SLIDE_LIST = new APIEndpoint(array(
	APIEndpoint::METHOD 		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE 	=> API_RESPONSE['JSON']
));
api_endpoint_init($SLIDE_LIST, auth_session_user());

$SLIDE_LIST->resp_set(array(
	'slides' => get_slides_id_list()
));
$SLIDE_LIST->send();

