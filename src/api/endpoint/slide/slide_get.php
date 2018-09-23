<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  ====>
*
*  *Get the data of a slide.*
*
*  GET parameters
*    * id = The id of the slide to get.
*
*  Return value
*    * slide
*
*      * id            = The ID of the slide.
*      * name          = The name of the slide.
*      * index         = The index of the slide.
*      * time          = The time the slide is shown.
*      * markup        = The markup of the slide.
*      * owner         = The owner of the slide.
*      * enabled       = Whether the slide is enabled or not.
*      * sched         = Whether the slide is scheduled or not.
*      * sched_t_s     = The slide schedule starting timestamp.
*      * sched_t_e     = The slide schedule ending timestamp.
*      * animation     = The slide animation identifier.
*      * collaborators = The collaborators of the slide.
*
*    * error   = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_GET = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => array(
		'id' => API_P_STR
	),
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
));
api_endpoint_init($SLIDE_GET);

$slide = new Slide();
$slide->load($SLIDE_GET->get('id'));
$SLIDE_GET->resp_set(
	['slide' => $slide->get_public_data_array()]
);
$SLIDE_GET->send();
