<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

function _verify_credentials($username, $password) {
	/*
	*  Verify that the login system has the user 'username' and
	*  that the password matches 'password'. Returns true
	*  if the verification is successful and false otherwise.
	*/

	$users_file = LIBRESIGNAGE_ROOT.USER_DATA_DIR.'/passwd.json';
	$users = json_decode(file_get_contents($users_file), $assoc=true);

	if (in_array($username, array_keys($users), true)) {
		if (password_verify($password, $users[$username])) {
			return true;
		}
	}
	return false;
}

function login($username, $password) {
	/*
	*  Attempt to login with $username and $password.
	*  Returns TRUE if the login succeeds and FALSE
	*  otherwise. The $_SESSION data is also set when
	*  the login succeeds.
	*/

	if (session_status() != PHP_SESSION_ACTIVE) {
		throw new Exception('No session active when attempted '.
				'to login.');
	}

	if (is_authorized()) {
		// Already logged in.
		return TRUE;
	}

	if (!empty($username) && !empty($password)) {
		if (_verify_credentials($username, $password)) {
			// Login success.
			$_SESSION['user'] = $username;
			return TRUE;
		}
	}
	// Login failed.
	return FALSE;
}

function logout() {
	/*
	*  Logout the currently logged in user. The session needs to
	*  be started by the caller before calling this function.
	*  If no session is active, this function throws an exception.
	*/

	if (session_status() != PHP_SESSION_ACTIVE) {
		throw new Exception('No session active when attempted '.
				'to logout.');
	}

	$_SESSION = array();

	if (ini_get('session.use_cookies')) {
		$cp = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$cp['path'], $cp['domain'],
			$cp['secure'], $cp['httponly']);
	}

	session_destroy();
}


function is_authorized($redir=false) {
	/*
	*  Check if the current session is authorized and
	*  return true if it is. False is returned otherwise.
	*  If 'redir' is true, this function also redirects
	*  the client to the login page.
	*/

	if (!empty($_SESSION['user'])) {
		return true;
	} else {
		if ($redir) {
			header('Location: '.LOGIN_PAGE);
			exit(0);
		}
		return false;
	}
}



