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

	// Setup debugging. Don't touch, set by mkdist.sh.
	const LIBRESIGNAGE_DEBUG = !!BCONST_DEBUG!!;

	// Instance owner config. Don't touch, set by mkdist.sh.
	const ADMIN_EMAIL = "!!BCONST_ADMIN_EMAIL!!";
	const ADMIN_NAME = "!!BCONST_ADMIN_NAME!!";

	/*
	*  Paths relative to document root. DO NOT make these absolute
	*  or system path information might be leaked to users.
	*/
	const SLIDES_DIR 			= "/data/slides";
	const QUEUES_DIR			= "/data/queues";
	const DOC_HTML_DIR			= "/doc/html";
	const DOC_RST_DIR			= "/doc/rst";
	const LICENSE_LS_RST	 		= "/doc/rst/LICENSE.rst";
	const LICENSES_EXT_RST		 	= "/doc/rst/LICENSES_EXT.rst";
	const NAV_PATH 				= "/common/php/nav/nav.php";
	const FOOTER_PATH 			= "/common/php/footer/footer.php";
	const FOOTER_MINIMAL_PATH 		= "/common/php/footer/footer_minimal.php";
	const USER_DATA_DIR 			= "/data/users";

	// Page constants.
	const LOGIN_PAGE 			= "/login";
	const LOGOUT_PAGE 			= "/logout";
	const DOCS_PAGE				= "/doc";
	const ABOUT_PAGE 			= "/doc?doc=about";
	const EDITOR_PAGE 			= "/control/editor";
	const CONTROL_PANEL_PAGE 		= "/control";
	const APP_PAGE 				= "/app";
	const USER_MGR_PAGE 			= "/control/usermgr";
	const USER_SETTINGS_PAGE 		= "/control/user";
	const ERROR_PAGES 			= "/errors";
	const LOGIN_LANDING 			= CONTROL_PANEL_PAGE;
	const LOGOUT_LANDING 			= LOGOUT_PAGE;

	/*
	*  Permanent cookie expiration date.
	*  (Tuesday, 19-Jan-2038 03:14:07 UTC)
	*/
	const PERMACOOKIE_EXPIRE		= 2147483647;

	const SESSION_MAX_AGE			= 600;
	const AUTH_TOKEN_LEN			= 15;

	// LibreSignage instance limits.
	const LS_LIM = array(
		"SLIDE_MIN_TIME" 		=> 1*1000,
		"SLIDE_MAX_TIME" 		=> 20*1000,
		"SLIDE_MAX_INDEX"		=> 65536,
		"SLIDE_NAME_MAX_LEN" 		=> 32,
		"SLIDE_MARKUP_MAX_LEN"	 	=> 2048,
		"SLIDE_MAX_COLLAB"		=> 64,
		"QUEUE_NAME_MAX_LEN"		=> 32,

		"MAX_USERS" 			=> 64,
		"MAX_USER_GROUPS" 		=> 32,
		"USERNAME_MAX_LEN"		=> 64,
		"PASSWORD_MAX_LEN"		=> 256,
		"API_RATE_T"			=> 60
	);

	function gtlim(string $lim) {
		return LS_LIM[$lim];
	}

	// User quota limits.
	const DEFAULT_QUOTA = array(
		'slides' => array(
			'limit' => 2,
			'disp' => 'Slides'
		),
		'api_rate' => array(
			'limit' => 100,
			'disp' => 'API quota (calls/'.
					LS_LIM['API_RATE_T'].'s)'
		)
	);

	/*
	*  Setup error handling and reporting.
	*/
	error_setup();
	error_set_debug(LIBRESIGNAGE_DEBUG);

	// Do some checks on the configured values.
	$max_slides = DEFAULT_QUOTA['slides']['limit']*gtlim('MAX_USERS');
	if ($max_slides > gtlim('SLIDE_MAX_INDEX') - 1) {
		throw new Exception('The configured slide quota '.
				'conflicts with the configured maximum '.
				'slide index value.');
	}
	// Prevent namespace pollution.
	unset($max_slides);
