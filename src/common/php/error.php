<?php
/*
*  LibreSignage error functionality.
*/

$ERROR_DEBUG = FALSE;
$ERROR_CODES = array(
	404 => '404 Not Found',
	403 => '403 Forbidden',
	500 => '500 Internal Server Error'
);

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

// Custom exception classes.
class ArgumentException extends Exception {};
class InternalException extends Exception {};

function error_set_debug(bool $debug) {
	global $ERROR_DEBUG;
	$ERROR_DEBUG = $debug;
}

function error_handle(int $code, Throwable $e = NULL) {
	/*
	*  When $ERROR_DEBUG == FALSE:
	*    Redirect the client to the error page corresponding
	*    to the HTTP error code $code. If $e != NULL, log it.
	*  When $ERROR_DEBUG == TRUE:
	*    Echo the exception to the client.
	*/
	global $ERROR_DEBUG, $ERROR_CODES;
	$tmp = $code;
	if (!array_key_exists($tmp, $ERROR_CODES)) {
		$tmp = 500;
	}

	if ($e && $ERROR_DEBUG) {
		header('Content-Type: text/plain');
		echo "\n### UNHANDLED EXCEPTION (HTTP: ".$code.") ###\n";
		echo $e->__toString();
		exit(0);
	}

	if ($e) {
		error_log($e->__toString());
	}
	header($_SERVER['SERVER_PROTOCOL'].$ERROR_CODES[$tmp]);
	header('Location: '.ERROR_PAGES.'/'.$code);
	exit(0);

}
