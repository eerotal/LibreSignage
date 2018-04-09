<?php
/*
*  ====>
*
*  *Request a new API key for a user. The API key used when this
*  endpoint was called is automatically expired when this call
*  finishes.*
*
*  Return value
*    * api_key = A newly generated API key for accessing the API.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$AUTH_REQ_API_KEY = new APIEndpoint(array(
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT		=> array(),
	APIEndpoint::REQ_QUOTA		=> FALSE,
	APIEndpoint::REQ_API_KEY	=> TRUE
));
api_endpoint_init($AUTH_REQ_API_KEY);

// Generate the new key.
$api_key = $AUTH_REQ_API_KEY->get_caller()->gen_api_key();

// Expire the old key.
$AUTH_REQ_API_KEY->get_caller()->rm_api_key(
	$AUTH_REQ_API_KEY->get_api_key()
);
$AUTH_REQ_API_KEY->get_caller()->write();

$AUTH_REQ_API_KEY->resp_set(array(
	'api_key' => $api_key,
	'error' => API_E_OK
));
$AUTH_REQ_API_KEY->send();
