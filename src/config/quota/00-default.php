<?php

/*
*  Default LibreSignage quota config. Don't edit this file directly.
*  Create a custom quota override file in conf/quota/ instead.
*/

return [
	'slides' => [
		'limit' => 10,
		'disp' => 'Slides'
	],
	'api_rate' => [
		'limit' => 200,
		'disp' => 'API quota (calls/'.LS_LIM['API_RATE_T'].'s)'
	]
];
