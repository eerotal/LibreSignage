<?php
/*
*  LibreSignage error functionality.
*/
namespace libresignage\common\php;

use libresignage\common\php\Log;

$ERROR_DEBUG = FALSE;

final class ErrorHandler {
	const NOT_FOUND = 404;
	const FORBIDDEN = 403;
	const INTERNAL_SERVER_ERROR = 500;

	const ERROR_STRINGS = [
		ErrorHandler::NOT_FOUND => '404 Not Found',
		ErrorHandler::FORBIDDEN => '403 Forbidden',
		ErrorHandler::INTERNAL_SERVER_ERROR => '500 Internal Server Error'
	];

	/**
	* Turn on/off debugging.
	*
	* @param bool $debug TRUE = debug on, FALSE = debug off.
	*/
	public static function set_debug(bool $debug) {
		global $ERROR_DEBUG;
		$ERROR_DEBUG = $debug;

		if ($debug) {
			error_reporting(E_ALL | E_NOTICE | E_STRICT);
			ini_set('display_errors', "1");
			ini_set('log_errors', "1");
			Log::enable(true);
		} else {
			error_reporting(0);
			ini_set('display_errors', "0");
			ini_set('log_errors', "0");
			Log::enable(false);
		}
	}

	/**
	* Set the default exeption and error handler functions. Note that these
	* may be overridden in other places. The API system, for example, uses
	* it's own exception handler.
	*/
	public static function setup() {
		set_exception_handler(function(\Throwable $e) {
			// Handle uncaught exceptions as internal errors.
			ErrorHandler::handle(self::INTERNAL_SERVER_ERROR, $e);
		});

		// Convert all errors to exceptions.
		set_error_handler(function($severity, $msg, $file, $line) {
			if ( !(error_reporting() & $severity) ) { return; }
			throw new \ErrorException($msg, 0, $severity, $file, $line);
		});
	}

	/**
	* Redirect the client to the error page corresponding to the HTTP
	* error code $code. Additionally echo the exception $e to the client
	* if $ERROR_DEBUG == TRUE and $e != NULL. If $ERROR_DEBUG == FALSE, the
	* exception is logged instead of echoing.
	*
	* @param int $code The HTTP error code.
	*
	* @param Throwable $e Optional error object to show to the caller.
	*/
	public static function handle(int $code, \Throwable $e = NULL) {
		global $ERROR_DEBUG;
		try {
			if (!array_key_exists($code, self::ERROR_STRINGS)) {
				$code = self::INTERNAL_SERVER_ERROR;
			}

			// Make sure we are actually getting a Throwable object.
			if ($e === NULL) {
				/*
				* From the line above $code is guaranteed to be listed in
				* self::ERROR_STRINGS. The default for unknown codes is
				* self::INTERNAL_SERVER_ERROR.
				*/
				$e = new \Exception(self::ERROR_STRINGS[$code], $code);
			}
			if ($ERROR_DEBUG) {
				echo "\n### Uncaught exception (HTTP: ".$code.") ###\n";
				echo $e->__toString();
				Log::logs($e->__toString(), Log::LOGERR);
			} else {
				header(
					$_SERVER['SERVER_PROTOCOL'].
					' '.
					self::ERROR_STRINGS[$code]
				);
				include(
					$_SERVER['DOCUMENT_ROOT'].
					'/../'.
					Config::config('ERROR_PAGES').
					'/'.
					$code.
					'/index.php'
				);
				Log::logs($e->__toString(), Log::LOGERR);
			}
		} catch (\Exception $e){
			/*
			*  Exceptions thrown in the exception handler cause
			*  hard to debug fatal errors. Handle them here.
			*/
			if ($ERROR_DEBUG) {
				echo "\ņ\nException thrown in global handler:\n";
				echo $e;
			}
		}
		exit(1);
	}
}
