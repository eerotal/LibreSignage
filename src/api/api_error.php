<?php
/*
*  API error definitions.
*/

// API errors
define("API_E_OK",		0);
define("API_E_INTERNAL",	1);
define("API_E_INVALID_REQUEST",	2);
define("API_E_NOT_AUTHORIZED",	3);

/*
*  Return detailed stack trace information with API errors.
*  DO NOT set this to TRUE on production systems.
*/
$API_ERROR_TRACE = TRUE;

function api_throw($errcode, $exception=NULL) {
	global $API_ERROR_TRACE;

	$err = array(
		'error' => $errcode
	);

	if ($API_ERROR_TRACE) {
		$bt = debug_backtrace();
		$err['thrown_at'] = $bt[0]['file'].' @ ln: '.
					$bt[0]['line'];
		if ($exception) {
			$err['e_msg'] = $exception->getMessage();
			$err['e_trace'] = $exception->getTraceAsString();
		}
	}

	$err_str = json_encode($err);
	if ($err_str == FALSE &&
		json_last_error() != JSON_ERROR_NONE) {
		echo '{"error": '.API_E_INTERNAL.'}';
		exit(0);
	}
	echo $err_str;
	exit(0);
}

