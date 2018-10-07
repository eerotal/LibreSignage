<?php
/*
*  ====>
*
*  Query specific data keys from all of the currently
*  existing slides.
*
*  **Request:** GET
*
*  parameters
*    * Data can be requested by assigning 1 to the
*      requested key. The following keys are accepted:
*      id, markup, name, index, time, owner, enabled,
*      sched, sched_t_s, sched_t_e, animation, collaborators,
*      lock
*
*  Return value
*    * data  = The requested data as nested dictionaries.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_DATA_QUERY = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::FORMAT_URL		=> array(
		'id' => API_P_STR|API_P_OPT,
		'markup' => API_P_STR|API_P_OPT,
		'name' => API_P_STR|API_P_OPT,
		'index' => API_P_STR|API_P_OPT,
		'time' => API_P_STR|API_P_OPT,
		'owner' => API_P_STR|API_P_OPT,
		'enabled' => API_P_STR|API_P_OPT,
		'sched' => API_P_STR|API_P_OPT,
		'sched_t_s' => API_P_STR|API_P_OPT,
		'sched_t_e' => API_P_STR|API_P_OPT,
		'animation' => API_P_STR|API_P_OPT,
		'collaborators' => API_P_STR|API_P_OPT,
		'lock' => API_P_STR|API_P_OPT
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE,
	APIEndpoint::STRICT_FORMAT	=> TRUE
));
api_endpoint_init($SLIDE_DATA_QUERY);

$ret = ['data' => []];
$tmp = new Slide();
$s_ids = slides_id_list();

foreach($s_ids as $s) {
	$tmp->load($s);
	$ret['data'][$s] = [];

	foreach($SLIDE_DATA_QUERY->get() as $k => $v) {
		$ret['data'][$s][$k] = $tmp->export(FALSE, FALSE)[$k];
	}
}
$SLIDE_DATA_QUERY->resp_set($ret);
$SLIDE_DATA_QUERY->send();
