<?php
/*
*  LibreSignage error functionality.
*/

$ERROR_DEBUG = FALSE;

const HTTP_ERR_404 = 404;
const HTTP_ERR_403 = 403;
const HTTP_ERR_500 = 500;

const ERROR_CODES = array(
	HTTP_ERR_404 => '404 Not Found',
	HTTP_ERR_403 => '403 Forbidden',
	HTTP_ERR_500 => '500 Internal Server Error'
);

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

// Custom exception classes.
class ArgException extends Exception {};
class IntException extends Exception {};
class FileTypeException extends Exception {};
class LimitException extends Exception {};
class ConfigException extends Exception {};

function error_set_debug(bool $debug) {
	/*
	*  Turn on or off debugging.
	*/
	global $ERROR_DEBUG;

	$ERROR_DEBUG = $debug;
	if ($debug) {
		error_reporting(E_ALL | E_NOTICE | E_STRICT);
		ini_set('display_errors', "1");
		ini_set('log_errors', "1");
	} else {
		error_reporting(E_ALL | ~E_NOTICE);
		ini_set('display_errors', "0");
		ini_set('log_errors', "1");
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
		if ( !(error_reporting() & $severity) ) {
			return;
		}
		throw new ErrorException(
			$msg, 0, $severity, $file, $line
		);
	});
}

function _notify_contact_admin() {
	echo "\n\n## Important! ##\n\n";
	echo "If you see this message and you aren't ".
		"the server admin,\nplease email the ".
		"admin at ".ADMIN_EMAIL." and copy this\n".
		"whole message (including the text above) ".
		"into the email.";
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
		$tmp = $code;
		if (!array_key_exists($tmp, ERROR_CODES)) {
			$tmp = HTTP_ERR_500;
		}

		if ($e && $ERROR_DEBUG) {
			header('Content-Type: text/plain');
			echo "\n### Uncaught exception ".
				"(HTTP: ".$code.") ###\n";
			echo $e->__toString();
			_notify_contact_admin();
			exit(1);
		} elseif ($e) {
			error_log($e->__toString());
		}

		header($_SERVER['SERVER_PROTOCOL'].' '.ERROR_CODES[$tmp]);
		include(
			$_SERVER['DOCUMENT_ROOT'].ERROR_PAGES.'/'.$tmp.'/index.php'
		);
		exit(1);
	} catch (Exception $e){
		/*
		*  Exceptions thrown in the exception handler cause
		*  hard to debug fatal errors. Handle them.
		*/
		if ($ERROR_DEBUG) {
			header('Content-Type: text/plain');
			echo "\n### ".get_class($e)." thrown in the ".
					"exception handler ###\n";
			echo $e->__toString();
			_notify_contact_admin();
		}
		exit(1);
	}
}
