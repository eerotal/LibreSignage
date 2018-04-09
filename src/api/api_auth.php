<?php
/*
*  API authentication functionality for LibreSignage. Web
*  interface authentication is handled in the file
*  common/php/auth/web_auth.php.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');

function api_creds_verify(string $user, string $pass) {
	/*
	*  Verify the supplied login credentials and return
	*  the corresponding User object if they are valid.
	*  NULL is returned otherwise.
	*/
	if (empty($user) || empty($pass) || !user_exists($user)) {
		return NULL;
	}

	$u = new User($user);
	if ($u) {
		if ($u->verify_password($pass)) {
			return $u;
		}
	}
	return NULL;
}

function api_key_verify(string $key) {
	/*
	*  Verify an API key and return the corresponding User
	*  object if the key is valid. Otherwise NULL is returned.
	*/
	if (!empty($key)) {
		$users = user_array();
		foreach ($users as $k => $u) {
			if ($u->verify_api_key($key)) {
				return $u;
			}
		}
	}
	return NULL;
}
