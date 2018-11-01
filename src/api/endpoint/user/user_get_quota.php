<?php
/*
*  ====>
*
*  Get a user's quota based on a username.
*
*  **Request:** GET
*
*  Parameters
*    * user = The username to query.
*
*  Return value
*    * quota      = A dictionary with the quota data.
*    * error      = An error code or API_E_OK on success.*
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');

$USER_GET_QUOTA = new APIEndpoint(array(
	APIEndpoint::METHOD           => API_METHOD['GET'],
	APIEndpoint::RESPONSE_TYPE    => API_MIME['application/json'],
	APIEndpoint::FORMAT_URL => [
		'user' => API_P_STR|API_P_OPT|API_P_NULL
	],
	APIEndpoint::REQ_QUOTA        => TRUE,
	APIEndpoint::REQ_AUTH         => TRUE
));

$flag_auth = FALSE;
$user_name = NULL;

if ($USER_GET_QUOTA->has('user', TRUE)) {
	// Get quota for the requested user.
	$user_name = $USER_GET_QUOTA->get('user');
} else {
	// Get quota for the logged in user.
	$user_name = $USER_GET_QUOTA->get_caller()->get_name();
}

// Allow admins or the user themself to get the quota.
if (!$USER_GET_QUOTA->get_caller()->is_in_group('admin')
	&& $USER_GET_QUOTA->get_caller()->get_name() != $user_name) {
	throw new APIException(
		API_E_NOT_AUTHORIZED,
		"Not authorized."
	);
}

try {
	$user = new User($user_name);
} catch (ArgException $e) {
	throw new APIException(
		API_E_INVALID_REQUEST,
		"Failed to load user.", 0, $e
	);
}
$user_quota = new UserQuota($user);

$USER_GET_QUOTA->resp_set(array(
	'quota' => $user_quota->get_quota_data()
));
$USER_GET_QUOTA->send();
