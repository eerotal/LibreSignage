<?php

/*
*  Default LibreSignage configuration. Don't edit this file directly.
*  Create a custom config override file in conf/available/ instead.
*/

return [
	// Version information.
	'LS_VER'                => "#pre(LS_VER)",
	'API_VER'               => "#pre(API_VER)",

	// Control debugging.
	'LIBRESIGNAGE_DEBUG'    => #pre(ICONF_DEBUG),

	// Default admin contact info.
	'ADMIN_EMAIL'           => "admin@example.com",
	'ADMIN_NAME'            => "admin",

	/*
	*  Paths relative to document root. DO NOT make these absolute
	*  or system path information might be leaked to users.
	*/
	'SLIDES_DIR'            => "/data/slides",
	'QUEUES_DIR'            => "/data/queues",
	'DOC_HTML_DIR'          => "/doc/html",
	'DOC_RST_DIR'           => "/doc/rst",
	'LICENSE_LS_RST'        => "/doc/rst/LICENSE.rst",
	'LICENSES_EXT_RST'      => "/doc/rst/LICENSES_EXT.rst",
	'NAV_PATH'              => "/common/php/nav/nav.php",
	'FOOTER_PATH'           => "/common/php/footer/footer.php",
	'USER_DATA_DIR'         => "/data/users",

	// Page constants.
	'LOGIN_PAGE'            => "/login",
	'LOGOUT_PAGE'           => "/logout",
	'DOCS_PAGE'             => "/doc",
	'ABOUT_PAGE'            => "/doc?doc=about",
	'EDITOR_PAGE'           => "/control/editor",
	'CONTROL_PANEL_PAGE'    => "/control",
	'APP_PAGE'              => "/app",
	'USER_MGR_PAGE'         => "/control/usermgr",
	'USER_SETTINGS_PAGE'    => "/control/user",
	'ERROR_PAGES'           => "/errors",
	'LOGIN_LANDING'         => CONTROL_PANEL_PAGE,
	'LOGOUT_LANDING'        => LOGOUT_PAGE,

	// Permanent cookie expiration on Tuesday, 19-Jan-2038 03:14:07 UTC
	'PERMACOOKIE_EXPIRE'    => 2147483647,
	'SESSION_MAX_AGE'       => 600,
	'AUTH_TOKEN_LEN'        => 15,
	'SLIDE_LOCK_MAX_AGE'    => 600,

	'ENABLE_FFMPEG_THUMBS'  => TRUE,
	'ENABLE_GD_THUMBS'      => TRUE,

	'FFMPEG_PATH'           => '/usr/bin/ffmpeg',
	'FFPROBE_PATH'          => '/usr/bin/ffprobe',

	'THUMB_MAXW'            => 320,
	'THUMB_MAXH'            => 180,
	'THUMB_EXT'             => '.png',

	'ASSET_FILENAME_MAXLEN' => 64,
];
