<?php
	/*
	*  An API handle to remove an existing slide.
	*
	*  POST parameters:
	*    * id = The id of the slide to remove.
	*
	*  Return value:
	*    A JSON encoded dictionary with the following data.
	*      * error = An error code or API_E_OK on success. **
	*
	*    ** (The error codes are listed in api_errors.php.)
	*/
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

	$SLIDE_RM = new APIEndpoint(
		$method = API_METHOD['POST'],
		$response_type = API_RESPONSE['JSON'],
		$format = array(
			'id' => API_P_STR
		)
	);
	api_endpoint_init($SLIDE_RM);

	$slide_list = get_slides_id_list();

	if (in_array($SLIDE_RM->get('id'), $slide_list)) {
		if (!rmdir_recursive(LIBRESIGNAGE_ROOT.SLIDES_DIR
				.'/'.$SLIDE_RM->get('id'))) {
			error_and_exit(API_E_INTERNAL);
		}
		echo json_encode(array("error" => API_E_OK));
		exit(0);
	}
	error_and_exit(API_E_INVALID_REQUEST);
