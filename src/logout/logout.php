<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

	function logout() {
		/*
		*  Logout by destroying the current session.
		*/

		if (session_status() != PHP_SESSION_ACTIVE) {
			return;
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
