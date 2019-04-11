<?php
/*
*  ====>
*
*  Get the data of the current user.
*
*  **Request:** GET
*
*  Return value
*    * user
*
*      * user     = The name of the user.
*      * groups   = The groups the user is in.
*
*    * error      = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

$USER_GET = new APIEndpoint(array(
	APIEndpoint::METHOD         => API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::FORMAT_URL     => array(),
	APIEndpoint::REQ_QUOTA      => TRUE,
	APIEndpoint::REQ_AUTH       => TRUE
));

$USER_GET->resp_set([
	'user' => $USER_GET->get_caller()->export(FALSE, FALSE)
]);
$USER_GET->send();

