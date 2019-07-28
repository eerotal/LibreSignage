<?php

/*
*  Default LibreSignage quota config. Don't edit this file directly.
*  Create a custom quota override file in conf/quota/ instead.
*/
return [
	// Quota for the maximum number of slides per user.
	'slides' => [
		'limit' => 10,
		'description' => 'Slides'
	],

	// API rate limiting quota. The limit is in calls/API_RATE_T which
	// is calls/60s by default. You can find the API_RATE_T constant
	// in the config file, although you probably don't want to change it.
	'api_rate' => [
		'limit' => 500,
		'description' => 'API rate quota'
	]
];
