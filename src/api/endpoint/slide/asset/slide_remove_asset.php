<?php
/*
*  ====>
*
*  Remove a slide asset based on its name.
*
*  **Request:** POST, application/json
*
*  JSON parameters
*    * id   = The ID of the Slide to access.
*    * name = The asset name to remove.
*
*  Return value
*    * error         = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_REMOVE_ASSET = new APIEndpoint([
	APIEndpoint::METHOD         => API_METHOD['POST'],
	APIEndpoint::REQUEST_TYPE   => API_MIME['application/json'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY    => [
		'id' => API_P_STR,
		'name' => API_P_STR
	],
	APIEndpoint::REQ_QUOTA      => TRUE,
	APIEndpoint::REQ_AUTH       => TRUE
]);
api_endpoint_init($SLIDE_REMOVE_ASSET);

$slide = new Slide();
$slide->load($SLIDE_REMOVE_ASSET->get('id'));

// Allow admins, slide owners and slide collaborators to remove assets.
if (!(
	check_perm(
		'grp:admin;',
		$SLIDE_REMOVE_ASSET->get_caller()
	)
	|| check_perm(
		'grp:editor&usr:'.$slide->get_owner().';',
		$SLIDE_REMOVE_ASSET->get_caller())
	|| (
		check_perm('grp:editor;', $SLIDE_REMOVE_ASSET->get_caller())
		&& in_array(
			$SLIDE_REMOVE_ASSET->get_caller()->get_name(),
			$slide->get_owner()
		)
	)
)) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		'Not authorized.'
	);
}

$slide->remove_uploaded_asset($SLIDE_REMOVE_ASSET->get('name'));
$slide->write();

$SLIDE_REMOVE_ASSET->send();
