<?php
/*
*  Authentication functionality for LibreSignage.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');

const COOKIE_AUTH_TOKEN = 'session_token';

// -- General authentication functions. --

function auth_creds_verify(string $user, string $pass) {
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

function auth_token_verify(string $tok) {
	/*
	*  Verify an authentication token and return the
	*  corresponding User object if the token is valid.
	*  Otherwise NULL is returned.
	*/
	if (!empty($tok)) {
		$users = user_array();
		foreach ($users as $k => $u) {
			if ($u->session_verify($tok)) {
				return $u;
			}
		}
	}
	return NULL;
}

// -- Web interface authentication functions. --

function web_auth($user_wl = NULL,
			$group_wl = NULL,
			bool $redir = FALSE,
			$token = NULL) {
	$u = NULL;
	if (empty($token)) {
		// Use authentication token from cookie.
		$u = web_auth_cookie_verify($redir);
	} else {
		// Use supplied authentication token.
		$u = auth_token_verify($token);
		if ($u == NULL) {
			if ($redir) {
				header('Location: '.LOGIN_PAGE);
				exit(0);
			} else {
				return NULL;
			}
		}
	}

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
	*  Verify the auth token in the token cookie. This function
	*  can be used to grant access to web pages.
	*/
	if (!empty($_COOKIE[COOKIE_AUTH_TOKEN])) {
		return auth_token_verify($_COOKIE[COOKIE_AUTH_TOKEN]);
	}
	return NULL;
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
