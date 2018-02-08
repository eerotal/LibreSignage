<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

	session_start();
	auth_init();

	// Redirect already logged in users to the landing page.
	if (auth_is_authorized()) {
		header('Location: '.LOGIN_LANDING);
		exit(0);
	}

	if (auth_login($_POST['user'], $_POST['pass'])) {
		header('Location: '.LOGIN_LANDING);
		exit(0);
	} else {
		header('Location: /login/?failed=1');
		exit(0);
	}
