<?php
/*
*  Authentication functionality for LibreSignage.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/constants.php');

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
	*  Verify an authentication token. If a matching session is
	*  found, an array with the keys 'session' and 'user' is
	*  returned. 'session' contains the matching Session object.
	*  'user' is the user object of the session. If a matching
	*  session is not found, NULL is returned.
	*/
	$session = NULL;
	if (!empty($tok)) {
		foreach (user_array() as $k => $u) {
			$session = $u->session_token_verify($tok);
			if ($session !== NULL) {
				return [
					'user' => $u,
					'session' => $session
				];
			}
		}
	}
	return NULL;
}

function auth_cookie_verify() {
	/*
	*  Verify an authentication token supplied in an
	*  authentication token cookie. See auth_token_verify()
	*  for the return values. Additionally NULL is returned
'	*  if no auth cookie is found.
	*/
	if (empty($_COOKIE[AUTH_TOKEN_COOKIE])) {
		return NULL;
	} else {
		return auth_token_verify($_COOKIE[AUTH_TOKEN_COOKIE]);
	}
}

function web_auth(
	$user_wl = NULL,
	$group_wl = NULL,
	bool $redir = FALSE,
	$token = NULL
) {
	$d = NULL;
	if (empty($token)) {
		$d = auth_cookie_verify(); // Auth w/ cookie.
	} else {
		$d = auth_token_verify($token); // Auth w/ token.
	}
	if ($d === NULL) {
		if ($redir) {
			header(
				'Location: '.LOGIN_PAGE.
				'?redir='.urlencode($_SERVER['REQUEST_URI'])
			);
			exit(0);
		}
		return NULL;
	}
	if ($user_wl) {
		if (!auth_user_whitelist($d['user'], $user_wl)) {
			if ($redir) { error_handle(HTTP_ERR_403, new ErrorException(ERROR_CODES[HTTP_ERR_403], HTTP_ERR_403)); }
			return NULL;
		}
	}
	if ($group_wl) {
		if (!auth_group_whitelist($d['user'], $group_wl)) {
			if ($redir) { error_handle(HTTP_ERR_403, new ErrorException(ERROR_CODES[HTTP_ERR_403], HTTP_ERR_403)); }
			return NULL;
		}
	}
	return $d;
}

function auth_user_whitelist(User $u, array $wl) {
	/*
	*  Return TRUE if the user $u is in the whitelist $wl.
	*  FALSE is returned otherwise.
	*/
	return array_search($u->get_name(), $wl, TRUE);
}

function auth_group_whitelist(User $u, array $wl) {
	/*
	*  Return TRUE if the user $u is in one of the groups in $wl.
	*  FALSE is returned otherwise.
	*/
	return count(array_intersect($u->get_groups(), $wl)) > 0;
}
