<?php

/*
*  Default LibreSignage configuration. Don't edit this file directly.
*  Create a custom config override file in conf/available/ instead.
*/
return [
	// Version information.
	'LS_VER'                => "0.0.0",
	'API_VER'               => "0",

	// Control debugging.
	'LIBRESIGNAGE_DEBUG'    => FALSE,

	// Default admin contact info.
	'ADMIN_EMAIL'           => "Example Admin",
	'ADMIN_NAME'            => "admin@example.com",

	/*
	*  Paths relative to document root. Do not make these absolute
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

	// Max age for sessions in seconds.
	'SESSION_MAX_AGE'       => 600,

	// Authentication token length in characters.
	'AUTH_TOKEN_LEN'        => 15,

	// Default UID length in characters.
	'DEFAULT_UID_LEN'       => 32,

	// Maximum slide lock age in seconds.
	'SLIDE_LOCK_MAX_AGE'    => 60,

	// Default generated password length in characters.
	'GENERATED_PASSWD_LEN'  => 10,

	// Enable/Disable video thumbnail generation by ffmpeg.
	// You must also set FFMPEG_PATH and FFPROBE_PATH if you
	// enable this.
	'ENABLE_FFMPEG_THUMBS'  => FALSE,

	// Enable/Disable image thumbnail generation by php-gd.
	'ENABLE_GD_THUMBS'      => FALSE,

	// ffmpeg and ffprobe system paths.
	'FFMPEG_PATH'           => '/usr/bin/ffmpeg',
	'FFPROBE_PATH'          => '/usr/bin/ffprobe',

	// Maximum thumbnail width and height in pixels.
	'THUMB_MAXW'            => 320,
	'THUMB_MAXH'            => 180,

	// Thumbnail mimetype.
	'THUMB_MIME'            => 'image/png',

	// Maximum log length in lines.
	'LOG_MAX_LEN'			=> 300,

	// Default Cache-Control: max-age value.
	'CACHE_DEFAULT_MAX_AGE' => 600
];
