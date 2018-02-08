<?php
	/*
	*  LibreSignage config code and constants.
	*/

	/*
	*  Build time flags
	*    !!BUILD_VERIFY_NOCONFIG!!
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/error.php');

	define("LIBRESIGNAGE_ROOT", $_SERVER['DOCUMENT_ROOT']);

	// Enable debugging. Never set this to TRUE on a production system.
	const LIBRESIGNAGE_DEBUG = TRUE;

	/*
	*  Paths relative to document root. DO NOT make these absolute
	*  or system path information might be leaked to users.
	*/
	const SLIDES_DIR 			= "/data/slides";
	const LIBRESIGNAGE_LICENSE_FILE_PATH 	= "/doc/LICENSE.md";
	const LIBRARY_LICENSES_FILE_PATH 	= "/doc/LIBRARY_LICENSES.md";
	const NAV_PATH 				= "/common/php/nav/nav.php";
	const FOOTER_PATH 			= "/common/php/footer/footer.php";
	const FOOTER_MINIMAL_PATH 		= "/common/php/footer/footer_minimal.php";
	const USER_DATA_DIR 			= "/data/users";

	// Page constants.
	const LOGIN_PAGE 			= "/login";
	const LOGOUT_PAGE 			= "/logout";
	const ABOUT_PAGE 			= "/about";
	const EDITOR_PAGE 			= "/control/editor";
	const CONTROL_PANEL_PAGE 		= "/control";
	const APP_PAGE 				= "/app";
	const USER_MGR_PAGE 			= "/control/usermgr";
	const USER_SETTINGS_PAGE 		= "/control/user";
	const ERROR_PAGES 			= "/errors";
	const LOGIN_LANDING 			= CONTROL_PANEL_PAGE;
	const LOGOUT_LANDING 			= LOGOUT_PAGE;

	// LibreSignage instance limits.
	const SLIDE_MIN_TIME 			= 1*1000;
	const SLIDE_MAX_TIME 			= 20*1000;
	const SLIDE_MAX_NAME_SIZE 		= 32;
	const SLIDE_MAX_MARKUP_SIZE 		= 2048;
	const MAX_USERS				= 50;

	// User quota limits.
	const DEFAULT_QUOTA = array(
		'slides' => array(
			'limit' => 20,
			'disp' => 'Slides'
		)
	);

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
