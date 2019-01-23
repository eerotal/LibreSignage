<?php

/*
*  Default LibreSignage limits. Don't edit this file directly.
*  Create a custom limit override file in conf/limits/ instead.
*/
return [
	"SLIDE_MIN_DURATION"       => 1*1000,
	"SLIDE_MAX_DURATION"       => 40*1000,
	"SLIDE_MAX_INDEX"          => 65536,
	"SLIDE_NAME_MAX_LEN"       => 32,
	"SLIDE_MARKUP_MAX_LEN"     => 2048,
	"SLIDE_MAX_COLLAB"         => 64,
	"SLIDE_ASSET_NAME_MAX_LEN" => 64,
	"SLIDE_ASSET_VALID_MIMES"  => [
		'image/jpeg',
		'image/gif',
		'image/png',
		'video/mp4',
		'video/ogg',
		'video/webm'
	],
	"SLIDE_MAX_ASSETS"         => 20,

	"QUEUE_NAME_MAX_LEN"       => 32,

	"MAX_USERS"                => 64,
	"MAX_USER_GROUPS"          => 32,
	"USERNAME_MAX_LEN"         => 64,
	"PASSWORD_MAX_LEN"         => 256,
	"API_RATE_T"               => 60
];
