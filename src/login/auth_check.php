<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

	function check_authorized($redir=false) {
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
