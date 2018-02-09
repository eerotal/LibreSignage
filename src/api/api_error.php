<?php
/*
*  API error definitions.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

// API errors
define("API_E_OK",		0);
define("API_E_INTERNAL",	1);
define("API_E_INVALID_REQUEST",	2);
define("API_E_NOT_AUTHORIZED",	3);
define("API_E_QUOTA_EXCEEDED",	4);
define("API_E_LIMITED",		5);
define("API_E_CLIENT",		6); // Only for client side use!

/*
*  Return detailed stack trace information with
*  API errors when this is TRUE.
*/
define("API_ERROR_TRACE", LIBRESIGNAGE_DEBUG);

function api_throw($errcode, $exception=NULL) {
	/*
	*  Throw the API error code $errcode. Additionally
	*  include exception information in the response
	*  if $exception != NULL and API_ERROR_TRACE is TRUE.
	*/
	$err = array(
		'error' => $errcode
	);

	if (API_ERROR_TRACE) {
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

