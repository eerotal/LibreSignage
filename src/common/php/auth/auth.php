<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');

$_AUTH_USERS = NULL;
$_AUTH_INITED = FALSE;

function _auth_error_on_no_session() {
	if (session_status() == PHP_SESSION_NONE) {
		throw new Exception('Auth: No session active.');
	}
}

function _auth_error_on_uninited(string $additional_msg = '') {
	global $_AUTH_INITED;
	if (!$_AUTH_INITED) {
		throw new Exception('Authentication system not '.
				'initialized. '.$additional_msg);
	}
}

function _auth_load_users() {
	/*
	*  Load all the users from the userdata files.
	*  Returns an array with User objects in it or
	*  throws an Exception on error.
	*/
	$users = array();
	$tmp = NULL;

	$users_data_dir = LIBRESIGNAGE_ROOT.USER_DATA_DIR;
	$user_dirs = @scandir($users_data_dir);

	if ($user_dirs === FALSE) {
		throw new Exception('Failed to scan user data dir!');
	}
	$user_dirs = array_diff($user_dirs, array('.', '..'));

	foreach ($user_dirs as $d) {
		if (!is_dir($users_data_dir.'/'.$d)) { continue; }
		array_push($users, new User($d));
	}
	return $users;
}

function auth_get_users() {
	global $_AUTH_USERS;
	_auth_error_on_uninited();
	return $_AUTH_USERS;
}

function _auth_get_user_by_name(string $username) {
	/*
	*  Get the User object for $username from the
	*  $users User object array. Throws an error if
	*  the authentication system is not initialized.
	*/
	_auth_error_on_uninited();
	if (empty($username)) {
		return NULL;
	}
	foreach (auth_get_users() as $u) {
		if ($u->get_name() == $username) {
			return $u;
		}
	}
	return NULL;
}

function _auth_verify_credentials(string $username, string $password) {
	/*
	*  Verify that the login system has the user 'username' and
	*  that the password matches 'password'. Returns the User object
	*  if the verification is successful and NULL otherwise.
	*  This function throws an exception if the authentication
	*  system is not initialized.
	*/
	_auth_error_on_uninited();
	$user_obj = _auth_get_user_by_name($username);
	if ($user_obj) {
		if ($user_obj->verify_password($password)) {
			return $user_obj;
		}
	}
	return NULL;
}

function auth_login(string $username, string $password) {
	/*
	*  Attempt to login with $username and $password.
	*  Returns TRUE if the login succeeds and FALSE
	*  otherwise. The $_SESSION data is also set when
	*  the login succeeds.
	*/
	_auth_error_on_no_session();
	if (auth_is_authorized()) {
		// Already logged in in the current session.
		return TRUE;
	}

	if (!empty($username) && !empty($password)) {
		$tmp = _auth_verify_credentials($username, $password);
		if ($tmp != NULL) {
			// Login success.
			$_SESSION['user'] = $tmp->get_session_data();
			return TRUE;
		}
	}

	// Login failed.
	return FALSE;
}

function auth_logout() {
	/*
	*  Logout the currently logged in user. The session needs to
	*  be started by the caller before calling this function.
	*  If no session is active, this function throws an exception.
	*/
	_auth_error_on_no_session();
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
	*  Check if the current session is authorized to access
	*  a page and return TRUE if it is. FALSE is returned
	*  otherwise.
	*
	*  $groups and $users can optionally be used to filter which
	*  groups or users can access a page. These arguments are simply
	*  whitelists of group and user names. If $groups and $users are both
	*  defined, the value of $both affects how authentication is done.
	*  If $both is TRUE, it is required that the logged in user is in
	*  a group listed in $groups and a user listed in $users. Otherwise
	*  only one of these conditions must be met. If both $groups and
	*  $users are NULL, access is granted to all logged in users.
	*
	*  If 'redir' is TRUE, this function also redirects the client to
	*  the login page or the HTTP 403 page if access is not granted.
	*
	*/
	_auth_error_on_no_session();
	$auth_u = FALSE;
	$auth_g = FALSE;

	if (!empty($_SESSION['user'])) {
		if ($groups == NULL && $users == NULL) {
			//  Don't load data from files when not needed.
			return TRUE;
		} else {
			_auth_error_on_uninited('auth_init() call required '.
					'when $groups != NULL or '.
					'$users != NULL in an '.
					'auth_is_authorized() call.');

			$user_obj = _auth_get_user_by_name(
				$_SESSION['user']['user']);

			if ($users != NULL) {
				if (in_array($_SESSION['user']['user'],
						$users)) {
					$auth_u = TRUE;
				}
			}
			if ($groups != NULL) {
				foreach ($groups as $g) {
					if ($user_obj->is_in_group($g)) {
						$auth_g = TRUE;
						break;
					}
				}
			}
			if (($both && $auth_g && $auth_u) ||
				(!$both && ($auth_g || $auth_u))) {

				return TRUE;
			} else {
				if ($redir) {
					error_handle(403);
				} else {
					return FALSE;
				}
			}
		}
	} else {
		if ($redir) {
			header('Location: '.LOGIN_PAGE);
			exit(0);
		}
		return FALSE;
	}
}

function auth_session_user() {
	_auth_error_on_no_session();
	return _auth_get_user_by_name($_SESSION['user']['user']);
}

function auth_init() {
	/*
	*  Initialize the authentication system. The caller must
	*  start a session before calling this function. If no
	*  session is active, this function throws an exception.
	*/
	global $_AUTH_USERS, $_AUTH_INITED;
	_auth_error_on_no_session();
	$_AUTH_USERS = _auth_load_users();
	$_AUTH_INITED = TRUE;
}
