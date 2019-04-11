<?php
/*
*  ====>
*
*  Login using the authentication system.
*
*  **Request:** POST, application/json
*
*  POST parameters
*    * username    = Username
*    * password    = Password
*    * who         = A string that identifies the caller.
*    * permanent   = Create permanent session. False by default.
*
*  Return value
*    * user = Current user data.
*    * session = Current session data.
*    * error = An error code or API_E_OK on success.
*
*  <====
*/

require_once(LIBRESIGNAGE_ROOT.'/api/api.php');

$AUTH_LOGIN = new APIEndpoint(array(
	APIEndpoint::METHOD         => API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE  => API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY => array(
		'username'  => API_P_STR,
		'password'  => API_P_STR,
		'who'       => API_P_STR,
		'permanent' => API_P_BOOL|API_P_OPT
	),
	APIEndpoint::REQ_QUOTA      => FALSE,
	APIEndpoint::REQ_AUTH       => FALSE
));

$user = auth_creds_verify(
	$AUTH_LOGIN->get('username'),
	$AUTH_LOGIN->get('password')
);

if ($user) {
	// Create a new session.
	$tmp = preg_match('/[^a-zA-Z0-9_-]/', $AUTH_LOGIN->get('who'));
	if ($tmp) {
		throw new APIException(
			API_E_INVALID_REQUEST,
			"Invalid characters in the 'who' parameter."
		);
	} else if ($tmp === NULL) {
		throw new APIException(
			API_E_INTERNAL,
			"preg_match() failed."
		);
	}

	if ($AUTH_LOGIN->has('permanent')) {
		$perm = $AUTH_LOGIN->get('permanent');
	} else {
		$perm = FALSE;
	}
	$auth_data = $user->session_new(
		$AUTH_LOGIN->get('who'),
		$_SERVER['REMOTE_ADDR'],
		$perm
	);
	$user->write();

	// Set the session token cookie.
	setcookie(
		$name = 'session_token',
		$value = $auth_data['token'],
		$expire = PERMACOOKIE_EXPIRE,
		$path = '/'
	);

	$AUTH_LOGIN->resp_set(array(
		'user' => $user->export(FALSE, FALSE),
		'session' => array_merge(
			$auth_data['session']->export(FALSE, FALSE),
			[ 'token' => $auth_data['token'] ]
		),
		'error' => API_E_OK
	));
} else {
	$AUTH_LOGIN->resp_set([ 'error' => API_E_INCORRECT_CREDS ]);
}
$AUTH_LOGIN->send();
