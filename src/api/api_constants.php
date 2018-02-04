<?php
	/*
	*  LibreSignage API call constants.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

	define("API_CONST", array(
		'API_K_NO_CONSTANT' => 0,
		'API_K_NULL'        => 1,
		'API_K_FALSE'       => 2,
		'API_K_TRUE'        => 3
	));

	function parse_api_constants(string $str) {
		/*
		*  Return the value corresponding to
		*  the API constant in $str.
		*/
		foreach (array_keys(API_CONST) as $k) {
			if ($str == '__'.$k.'__') {
				return API_CONST[$k];
			}
		}
	}
