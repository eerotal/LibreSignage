<?php
/*
*  LibreSignage error functionality.
*/
require_once(LIBRESIGNAGE_ROOT.'/common/php/log.php');

$ERROR_DEBUG = FALSE;

const HTTP_ERR_404 = 404;
const HTTP_ERR_403 = 403;
const HTTP_ERR_500 = 500;

const ERROR_CODES = array(
	HTTP_ERR_404 => '404 Not Found',
	HTTP_ERR_403 => '403 Forbidden',
	HTTP_ERR_500 => '500 Internal Server Error'
);

// Custom exception classes.
class ArgException extends Exception {};
class IntException extends Exception {};
class FileTypeException extends Exception {};
class LimitException extends Exception {};
class QuotaException extends Exception {};
class ConfigException extends Exception {};

function error_set_debug(bool $debug) {
	/*
	*  Turn on/off debugging.
	*/
	global $ERROR_DEBUG;
	$ERROR_DEBUG = $debug;

	if ($debug) {
		error_reporting(E_ALL | E_NOTICE | E_STRICT);
		ini_set('display_errors', "1");
		ini_set('log_errors', "1");
		ls_log_enable(true);
	} else {
		error_reporting(0);
		ini_set('display_errors', "0");
		ini_set('log_errors', "0");
		ls_log_enable(false);
	}
}

function error_setup() {
	/*
	*  Set the default exeption and error handler functions.
	*  Note that these may be overridden in other places. The
	*  API system, for example, uses it's own exception handler.
	*/
	set_exception_handler(function(Throwable $e) {
		// Handle uncaught exceptions as internal errors.
		error_handle(HTTP_ERR_500, $e);
	});

	// Convert all errors to exceptions.
	set_error_handler(function($severity, $msg, $file, $line) {
		if ( !(error_reporting() & $severity) ) { return; }
		throw new ErrorException($msg, 0, $severity, $file, $line);
	});
}

function error_handle(int $code, Throwable $e = NULL) {
	/*
	*  Redirect the client to the error page corresponding
	*  to the HTTP error code $code. Additionally echo the
	*  exception $e to the client if $ERROR_DEBUG == TRUE
	*  and $e != NULL. If $ERROR_DEBUG == FALSE, the
	*  exception is logged instead of echoing.
	*/
	global $ERROR_DEBUG;
	try {
		if (!array_key_exists($code, ERROR_CODES)) { $code = HTTP_ERR_500; }
		if ($e && $ERROR_DEBUG) {
			header('Content-Type: text/plain');
			echo "\n### Uncaught exception (HTTP: ".$code.") ###\n";
			echo $e->__toString();
			ls_log($e->__toString(), LOGERR);
		} else {
			header($_SERVER['SERVER_PROTOCOL'].' '.ERROR_CODES[$code]);
			include($_SERVER['DOCUMENT_ROOT'].ERROR_PAGES.'/'.$code.'/index.php');
			ls_log($e->__toString(), LOGERR);
		}
		header($_SERVER['SERVER_PROTOCOL'].' '.ERROR_CODES[$tmp]);
		include(LIBRESIGNAGE_ROOT.ERROR_PAGES.'/'.$tmp.'/index.php');
		exit(1);
	} catch (Exception $e){
		/*
		*  Exceptions thrown in the exception handler cause
		*  hard to debug fatal errors. Handle them.
		*/
		if ($ERROR_DEBUG) {
			header('Content-Type: text/plain');
			echo "\n### ".get_class($e)." thrown in the exception handler ###\n";
			echo $e->__toString();
		}
	}
	exit(1);
}
