<?php
	/*
	*  LibreSignage config code and constants.
	*/

	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	define("LIBRESIGNAGE_ROOT",			$_SERVER['DOCUMENT_ROOT']);

	/*
	*  Paths relative to document root. DO NOT make these absolute
	*  or system path information might be leaked to users.
	*/
	define("SLIDES_DIR", 				"/data/slides");
	define("LIBRESIGNAGE_LICENSE_FILE_PATH",	"/doc/LICENSE.md");
	define("LIBRARY_LICENSES_FILE_PATH",		"/doc/LIBRARY_LICENSES.md");
	define("FOOTER_PATH",				"/global_php/footer/footer.php");
	define("FOOTER_MINIMAL_PATH",			"/global_php/footer/footer_minimal.php");
	define("USER_DATA_DIR",				"/data/users");

	define("LOGIN_PAGE", 				"/login");
	define("LOGIN_LANDING", 			"/control");
	define("LOGOUT_LANDING",			"/logout");
