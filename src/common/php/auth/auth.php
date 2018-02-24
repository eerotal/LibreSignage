<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');

function _auth_chk_session() {
	if (session_status() == PHP_SESSION_NONE) {
		throw new IntException("No active session.");
	}
}

function auth_verify(string $username, string $password) {
	/*
	*  Verify that the auth system has the user $username and
	*  that the password matches $password. Returns the User
	*  object if the verification is successful and NULL otherwise.
	*/
	if (!user_exists($username)) {
		return NULL;
	}

	$usr = new User($username);
	if ($usr) {
		if ($usr->verify_password($password)) {
			return $usr;
		}
	}
	return NULL;
}

function auth_login(string $username, string $password) {
	/*
	*  Attempt to login with $username and $password.
	*  Returns TRUE if the login succeeds and FALSE
	*  otherwise. The $_SESSION data is also set when
	*  the login succeeds. A session needs to be started
	*  before calling this function.
	*/
	_auth_chk_session();
	if (auth_is_authorized()) {
		// Already logged in.
		return TRUE;
	}

	if (!empty($username) && !empty($password)) {
		$tmp = auth_verify($username, $password);
		if ($tmp != NULL) {
			$_SESSION['user'] = $tmp->get_name();
			return TRUE;
		}
	}
	return FALSE;
}

function auth_logout() {
	/*
	*  Logout the currently logged in user. A session
	*  needs to be started by the caller before calling
	*  this function.
	*/
	_auth_chk_session();
	$_SESSION = array();

	if (ini_get('session.use_cookies')) {
		$cp = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$cp['path'], $cp['domain'],
			$cp['secure'], $cp['httponly']);
	}
	session_destroy();
}

function auth_is_authorized(array $groups = NULL,
				array $users = NULL,
				bool $redir = FALSE,
				bool $both = FALSE) {
	/*
	*  Check whether the current session is authorized to access
	*  a page and return TRUE if it is. FALSE is returned
	*  otherwise.
	*
	*  $groups and $users can optionally be used to filter which
	*  groups or users can access a page. These arguments are simply
	*  whitelists of group and user names. If $groups and $users are
	*  both defined, the value of $both affects how authentication is
	*  done. If $both is TRUE, it is required that the logged in user
	*  is in a group listed in $groups and a user listed in $users.
	*  Otherwise only one of these conditions must be met.
	*
	*  If both $groups and $users are NULL and $both is FALSE, access
	*  is granted to all logged in users.
	*
	*  If $redir is TRUE, this function also redirects the client to
	*  the login page if the user is not logged in or to the HTTP
	*  403 page if access is not granted.
	*
	*/
	_auth_chk_session();
	$auth_u = FALSE;
	$auth_g = FALSE;
	$usr = auth_session_user();

	if ($usr == NULL) {
		if ($redir) {
			header('Location: '.LOGIN_PAGE);
			exit(0);
		}
		return FALSE;
	}

	if (!$both && $groups == NULL && $users == NULL) {
		return TRUE;
	} else {
		if ($users != NULL) {
			if (in_array($usr->get_name(), $users)) {
				$auth_u = TRUE;
			}
		}
		if ($groups != NULL) {
			foreach ($groups as $g) {
				if ($usr->is_in_group($g)) {
					$auth_g = TRUE;
					break;
				}
			}
		}
		if ($both) {
			if ($auth_g && $auth_u) {
				return TRUE;
			}
		} else {
			if ($auth_g || $auth_u) {
				return TRUE;
			}
		}

		// ==> Not authorized.
		if ($redir) {
			error_handle(HTTP_ERR_403);
		} else {
			return FALSE;
		}
	}
}

function auth_session_user() {
	$user = NULL;
	_auth_chk_session();
	if (empty($_SESSION['user'])) { return NULL; }
	try {
		$user = new User($_SESSION['user']);
	} catch (ArgException $e) {
		/*
		*  Logout since the current userdata in
		*  $_SESSION is invalid.
		*/
		auth_logout();
		session_start();
		$user = NULL;
	}
	return $user;
}
