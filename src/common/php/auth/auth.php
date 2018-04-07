<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');

function auth_verify(string $username, string $password) {
	/*
	*  Verify the $username - $password combination. Returns the
	*  corresponding User object if the verification is successful
	*  and NULL otherwise.
	*/
	if (empty($username) ||
		empty($password) ||
		!user_exists($username)) {
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

function auth_login($username, $password) {
	/*
	*  Login using a username and password. Returns the corresponding
	*  User object on success and NULL otherwise.
	*/

	// Already authenticated?
	if (auth_is_authorized()) {
		return auth_session_user();
	}

	$tmp = NULL;
	if (!empty($username) && !empty($password)) {
		$tmp = auth_verify($username, $password);
		if ($tmp != NULL) {
			$_SESSION['user'] = $tmp->get_name();
			return $tmp;
		}
	}
	return NULL;
}

function auth_logout() {
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
	*/
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
	if (empty($_SESSION['user'])) { return NULL; }
	try {
		// Attempt to load the userdata.
		$user = new User($_SESSION['user']);
	} catch (ArgException $e) {
		// Logout since the current username is invalid.
		auth_logout();
		session_start();
		$user = NULL;
	}
	return $user;
}

function auth_setup() {
	/*
	*  Setup the authentication system.
	*  - Start a new session.
	*  - Set the 'auth_setup' flag in $_SESSION.
	*/
	session_start();

	// Return if the setup is already done.
	if (array_key_exists('auth_setup', $_SESSION) &&
		$_SESSION['auth_setup']) {
		return;
	}
}

// Automatically setup the authentication system.
auth_setup();
