<?php

namespace common\php;

use \common\php\Exceptions\IntException;

$LS_LOG_ENABLED = FALSE;

// Define log destinations.
define('LOGDEF', LOG_DIR."/default.log");
define('LOGERR', LOG_DIR."/error.log");

/**
* Logging functions for LibreSignage.
*/
final class Log {
	/**
	* Enable/disable logging.
	*
	* @param bool $state TRUE = log enabled, FALSE = log disabled.
	*/
	public static function enable(bool $state) {
		global $LS_LOG_ENABLED;
		$LS_LOG_ENABLED = $state;
	}

	/**
	*  Open and lock a log file.
	*
	* @param string $log The logfile path.
	* @param int $lock Lock type (LOCK_EX, LOCK_SH).
	* @param string $mode The mode to open the file in. See PHP docs for fopen().
	* @return resource The opened file handle.
	* @throws IntException if opening the file fails.
	* @throws IntException if locking the file fails.
	*/
	private static function open(string $log, int $lock, string $mode) {
		$handle = fopen($log, $mode);
		if ($handle === FALSE) {
			throw new IntException("Failed to create log file '$log'.");
		}
		if (flock($handle, $lock) === FALSE) {
			throw new IntException("flock('$log', ...) failed.");
		}
		return $handle;
	}

	/**
	* Unlock and close a log file.
	*
	* @param resource $hanle The file handle to unlock and close.
	* @throws AssertionError if $handle is not a resource.
	* @throws AssertionError if $handle is not an open file handle.
	* @throws IntException if unlocking the file fails.
	* @throws IntException if closing the file handle fails.
	*/
	private static function close($handle) {
		assert(
			is_resource($handle),
			'$handle must be a resource.'
		);
		assert(
			get_resource_type($handle) ==='stream',
			'$handle must be an open file handle'
		);

		if (flock($handle, LOCK_UN) === FALSE) {
			throw new IntException("flock('$log', LOCK_UN) failed.");
		}
		if (fclose($handle) === FALSE) {
			throw new IntException("fclose() on '$log' failed.");
		}
	}

	/**
	* Truncate a log file to $len lines by deleting lines starting
	* from the beginning of the file.
	*
	* @param int $len The number of lines to truncate the file to.
	* @param string $log The path to the log file.
	*/
	private static function truncate(int $len, string $log) {
		$ln = '';
		$lines = [];

		// Read old log contents.
		$handle = self::open($log, LOCK_EX, 'r');
		while (($ln = fgets($handle)) !== FALSE) { $lines[] = $ln; }
		self::close($handle);

		// Write truncated contents.
		if (count($lines) <= $len) { return; }
		$handle = self::open($log, LOCK_EX, 'w');
		for ($i = count($lines) - $len; $i < count($lines); $i++) {
			fwrite($handle, $lines[$i]);
		}
		self::close($handle);
	}

	/**
	* Log a message into one of the log files.
	*
	* @param string $msg The message to log.
	* @param string $log The logfile to use. Default is LOGDEF.
	* @throws IntException if creating the log directory fails.
	* @throws IntException if writing to the logfile fails.
	*/
	public static function logs(string $msg, string $log = LOGDEF) {
		global $LS_LOG_ENABLED;
		if (!$LS_LOG_ENABLED) { return; }

		if (!is_dir(dirname($log))) {
			if (!mkdir(dirname($log))) {
				throw new IntException('Failed to create log directory.');
			}
		}
		$handle = self::open($log, LOCK_EX, 'a');

		$bt = debug_backtrace();
		if (count($bt) !== 0) { $file = $bt[0]['file']; }

		if (fwrite($handle, "[$file] [".date('r')."] ".$msg."\n") === FALSE) {
			throw new IntException("fwrite() on '$log' failed.");
		}
		self::close($handle);
		self::truncate(LOG_MAX_LEN, $log);
	}
}
