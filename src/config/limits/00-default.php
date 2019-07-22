<?php

/*
* Default LibreSignage limits. Don't edit this file directly.
* Create a custom limit override file in conf/limits/ instead.
*/
return [
	// Minimum slide duration in milliseconds.
	"SLIDE_MIN_DURATION"       => 1*1000,

	// Maximum slide duration in milliseconds.
	"SLIDE_MAX_DURATION"       => 40*1000,

	// Maximum slide index.
	"SLIDE_MAX_INDEX"          => 65536,

	// Maximum slide name length in characters.
	"SLIDE_NAME_MAX_LEN"       => 32,

	// Maximum slide markup length in characters.
	"SLIDE_MARKUP_MAX_LEN"     => 2048,

	// Maximum number of collaborators for a slide.
	"SLIDE_MAX_COLLAB"         => 64,

	// Maximum slide asset filename length in characters.
	"SLIDE_ASSET_NAME_MAX_LEN" => 64,

	// Allowed mimetypes for slide assets.
	"SLIDE_ASSET_VALID_MIMES"  => [
		'image/jpeg',
		'image/gif',
		'image/png',
		'video/mp4',
		'video/ogg',
		'video/webm'
	],

	// Maximum number of assets for a slide.
	"SLIDE_MAX_ASSETS"         => 20,

	// Maximum queue name length in characters.
	"QUEUE_NAME_MAX_LEN"       => 32,

	// Maximum number of users.
	"MAX_USERS"                => 64,

	// Maximum number of groups for a user.
	"MAX_USER_GROUPS"          => 32,

	// Maximum group name length in characters.
	"MAX_USER_GROUP_LEN"       => 64,

	// Maximum username length in characters.
	"USERNAME_MAX_LEN"         => 64,

	// Maximum password length in characters.
	"PASSWORD_MAX_LEN"         => 256,

	// The timewindow used for API ratelimiting. You probably don't
	// want to change this.
	"API_RATE_T"               => 60
];
