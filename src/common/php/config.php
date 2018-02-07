<?php
	/*
	*  LibreSignage config code and constants.
	*/

	/*
	*  Build time flags
	*    !!BUILD_VERIFY_NOCONFIG!!
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/error.php');

	// Enable debugging. Never set this to TRUE on a production system.
	define("LIBRESIGNAGE_DEBUG",			TRUE);

	define("LIBRESIGNAGE_ROOT",			$_SERVER['DOCUMENT_ROOT']);

	/*
	*  Paths relative to document root. DO NOT make these absolute
	*  or system path information might be leaked to users.
	*/
	define("SLIDES_DIR", 				"/data/slides");
	define("LIBRESIGNAGE_LICENSE_FILE_PATH",	"/doc/LICENSE.md");
	define("LIBRARY_LICENSES_FILE_PATH",		"/doc/LIBRARY_LICENSES.md");
	define("NAV_PATH",				"/common/php/nav/nav.php");
	define("FOOTER_PATH",				"/common/php/footer/footer.php");
	define("FOOTER_MINIMAL_PATH",			"/common/php/footer/footer_minimal.php");
	define("USER_DATA_DIR",				"/data/users");

	define("LOGIN_PAGE", 				"/login");
	define("LOGOUT_PAGE", 				"/logout");
	define("ABOUT_PAGE",				"/about");
	define("EDITOR_PAGE",				"/control/editor");
	define("CONTROL_PANEL_PAGE",			"/control");
	define("APP_PAGE",				"/app");
	define("USER_MGR_PAGE",				"/control/usermgr");
	define("USER_SETTINGS_PAGE",			"/control/user");

	define("LOGIN_LANDING", 			CONTROL_PANEL_PAGE);
	define("LOGOUT_LANDING",			LOGOUT_PAGE);

	define("ERRORS",				"/errors");

	define("SLIDE_MIN_TIME",			1*1000);
	define("SLIDE_MAX_TIME",			20*1000);
	define("SLIDE_MAX_NAME_SIZE",			32);
	define("SLIDE_MAX_MARKUP_SIZE",			2048);


	/*
	*  Setup error handling and reporting.
	*/
	if (LIBRESIGNAGE_DEBUG) {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		error_set_debug(TRUE);
	}

	set_exception_handler(function(Throwable $e) {
		error_handle(500, $e);
	});
