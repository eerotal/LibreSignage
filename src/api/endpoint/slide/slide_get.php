<?php
	/*
	*  API handle to get the JSON encoded data of a slide.
	*
	*  GET parameters:
	*    * id = The id of the slide to get.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * id      = The ID of the slide. **
	*      * name    = The name of the slide. **
	*      * index   = The index of the slide. **
	*      * time    = The time the slide is shown. **
	*      * markup  = The markup of the slide. **
	*      * error   = An error code or API_E_OK on success. ***
	*
	*    **  (Only exists if the API call was successful.)
	*    *** (The error codes are listed in api_errors.php.)
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');

	$SLIDE_GET = new APIEndpoint(
		$method = API_METHOD['GET'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'id' => API_P_STR
		)
	);
	api_endpoint_init($SLIDE_GET);

	$list = get_slides_id_list();

	if (in_array($SLIDE_GET->get('id'), $list)) {
		// Get by ID.
		$slide = new Slide();
		try {
			$slide->load($SLIDE_GET->get('id'));
		} catch (Exception $e) {
			api_throw(API_E_INTERNAL, $e);
		}

		$ret = $slide->get_data();
		$ret['error'] = 0;
		$ret_str =  json_encode($ret);
		if ($ret_str === FALSE) {
			api_throw(API_E_INTERNAL);
		}
		echo $ret_str;
		exit(0);
	}
	api_throw(API_E_INVALID_REQUEST);
