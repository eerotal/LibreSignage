<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/login/auth_check.php');

	function login_redir($uri) {
		/*
		*  Redirect the client to 'uri'.
		*/
		header('Location: '.$uri);
		exit(0);
	}

	function verify_login($username, $password) {
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

	session_start();

	if (check_authorized()) {
		// Already logged in.
		login_redir(LOGIN_LANDING);
	}

	if (!empty($_POST['user']) && !empty($_POST['pass'])) {
		if (verify_login($_POST['user'], $_POST['pass'])) {
			// Login success.
			$_SESSION['user'] = $_POST['user'];
			login_redir(LOGIN_LANDING);
		}
	}

	// Login failed.
	login_redir('/login/?failed=1');


