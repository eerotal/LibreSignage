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
	'LIBRESIGNAGE_DEBUG'    => #pre(CONF_DEBUG),

	// Default admin contact info.
	'ADMIN_EMAIL'           => "#pre(CONF_ADMIN_EMAIL)",
	'ADMIN_NAME'            => "#pre(CONF_ADMIN_NAME)",

	/*
	*  Paths relative to document root. DO NOT make these absolute
	*  or system path information might be leaked to users.
	*/
	'SLIDES_DIR'            => "/data/slides",
	'QUEUES_DIR'            => "/data/queues",
	'DOC_HTML_DIR'          => "/public/doc/html",
	'DOC_RST_DIR'           => "/public/doc/rst",
	'LICENSE_LS_RST'        => "/public/doc/rst/LICENSE.rst",
	'LICENSES_EXT_RST'      => "/public/doc/rst/LICENSES_EXT.rst",
	'NAV_PATH'              => "/common/php/nav/nav.php",
	'FOOTER_PATH'           => "/common/php/footer/footer.php",
	'USER_DATA_DIR'         => "/data/users",
	'LOG_DIR'               => "/var/log/libresignage",

	// Page constants.
	'LOGIN_PAGE'            => "/login",
	'LOGOUT_PAGE'           => "/logout",
	'DOCS_PAGE'             => "/doc",
	'README_PAGE'           => "/doc?doc=README",
	'EDITOR_PAGE'           => "/control/editor",
	'CONTROL_PANEL_PAGE'    => "/control",
	'APP_PAGE'              => "/app",
	'USER_MGR_PAGE'         => "/control/usermgr",
	'USER_SETTINGS_PAGE'    => "/control/user",
	'ERROR_PAGES'           => "/public/errors",
	'LOGIN_LANDING'         => "/control",
	'LOGOUT_LANDING'        => "/logout",

	// Permanent cookie expiration on Tuesday, 19-Jan-2038 03:14:07 UTC
	'PERMACOOKIE_EXPIRE'    => 2147483647,
	'SESSION_MAX_AGE'       => 600,
	'AUTH_TOKEN_LEN'        => 15,
	'DEFAULT_UID_LEN'       => 32,
	'SLIDE_LOCK_MAX_AGE'    => 60,
	'GENERATED_PASSWD_LEN'  => 10,

	'ENABLE_FFMPEG_THUMBS'  => #pre(CONF_FEATURE_VIDTHUMBS),
	'ENABLE_GD_THUMBS'      => #pre(CONF_FEATURE_IMGTHUMBS),

	'FFMPEG_PATH'           => '/usr/bin/ffmpeg',
	'FFPROBE_PATH'          => '/usr/bin/ffprobe',

	'THUMB_MAXW'            => 320,
	'THUMB_MAXH'            => 180,
	'THUMB_EXT'             => '.png',

	'ASSET_FILENAME_MAXLEN' => 64,

	'LOG_MAX_LEN'			=> 100
];
