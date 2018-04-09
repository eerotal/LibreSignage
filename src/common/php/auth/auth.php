<?php
/*
*  Web interface authentication functionality for LibreSignage.
*  API authentication is handled in the file api/api_auth.php.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_auth.php');

const COOKIE_API_KEY = 'api_key';

function web_auth($user_wl = NULL, $group_wl = NULL, bool $redir = FALSE) {
	$u = web_auth_cookie_verify($redir);
	if (!$u) {
		if ($redir) {
			header('Location: '.LOGIN_PAGE);
			exit(0);
		}
		return NULL;
	}
	if ($user_wl) {
		if (!web_auth_user_whitelist($u, $user_wl)) {
			if ($redir) {
				error_handle(HTTP_ERR_403);
			}
			return NULL;
		}
	}
	if ($group_wl) {
		if (!web_auth_group_whitelist($u, $group_wl)) {
			if ($redir) {
				error_handle(HTTP_ERR_403);
			}
			return NULL;
		}
	}
	return $u;
}

function web_auth_cookie_verify(bool $redir = FALSE) {
	/*
	*  Verify the API key in the API key cookie. This function
	*  can be used to grant access to web pages. The api_key_verify()
	*  function is used for API calls.
	*/
	if (!empty($_COOKIE[COOKIE_API_KEY])) {
		return api_key_verify($_COOKIE[COOKIE_API_KEY]);
	}
	return FALSE;
}

function web_auth_user_whitelist(User $u, array $wl) {
	/*
	*  Return TRUE if the user $u is in the whitelist $wl.
	*  FALSE is returned otherwise.
	*/
	return array_search($u->get_name(), $wl, TRUE);
}

function web_auth_group_whitelist(User $u, array $wl) {
	/*
	*  Return TRUE if the user $u is in one of the groups in $wl.
	*  FALSE is returned otherwise.
	*/
	return count(array_intersect($u->get_groups(), $wl)) > 0;
}
