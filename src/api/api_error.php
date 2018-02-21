<?php
/*
*  API error definitions.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

// API errors
const API_E = array(
	"API_E_OK"		=> 0,
	"API_E_INTERNAL"	=> 1,
	"API_E_INVALID_REQUEST"	=> 2,
	"API_E_NOT_AUTHORIZED"	=> 3,
	"API_E_QUOTA_EXCEEDED"	=> 4,
	"API_E_LIMITED"		=> 5,
	"API_E_CLIENT"		=> 6, // Only for client side use!
	"API_E_RATE"		=> 7
);

// Define the error codes in the symbol table too.
foreach (API_E as $err => $code) {
	define($err, $code);
}

const API_E_MSG = array(
	API_E_OK => array(
		"short" =>"No error",
		"long" => "No error occured."
	),
	API_E_INTERNAL => array(
		"short" => "Internal server error",
		"long" => "The server encountered an internal server error."
	),
	API_E_INVALID_REQUEST => array(
		"short" => "Invalid request",
		"long" => "The server responded with and invalid request ".
			"error. This is probably due to a software bug."
	),
	API_E_NOT_AUTHORIZED => array(
		"short" => "Not authorized",
		"You are not authorized to perform this action."
	),
	API_E_QUOTA_EXCEEDED => array(
		"short" => "Quota exceeded",
		"long" => "You have exceeded your quota for this action."
	),
	API_E_LIMITED => array(
		"short" => "Limited",
		"long" => "The server prevented this action because a ".
			"server limit would have been exceeded."
	),
	API_E_CLIENT => array(
		"short" => "Client error",
		"long" => "The client encountered an error."
	),
	API_E_RATE => array(
		"short" => "API rate limited",
		"long" => "The server ignored an API call because the ".
			"API rate limit was exceeded."
	)
);

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

