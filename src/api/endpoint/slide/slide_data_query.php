<?php
/*
*  ====>
*
*  *Query specific data keys from all of the currently
*  existing slides.*
*
*  GET parameters
*    * Data can be requested by just assigning the key name
*      a value such as 1 in the GET parameters.
*
*  Return value
*    * data  = The requested data as nested dictionaries.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

$SLIDE_DATA_QUERY = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array(),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($SLIDE_DATA_QUERY);

// Check that the requested keys are in the required keys array.
if (!count($SLIDE_DATA_QUERY->get()) || !array_is_subset(
	array_keys($SLIDE_DATA_QUERY->get()), Slide::REQ_KEYS)) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Invalid query keys."
	);
}

$slides = get_slides_id_list();
$ret = array('data' => array());
$tmp_slide = new Slide();

foreach($slides as $s) {
	$tmp_slide->load($s);

	$ret['data'][$s] = array();
	foreach(array_keys($SLIDE_DATA_QUERY->get()) as $k) {
		$ret['data'][$s][$k] = $tmp_slide->get($k);
	}
}
$SLIDE_DATA_QUERY->resp_set($ret);
$SLIDE_DATA_QUERY->send();
