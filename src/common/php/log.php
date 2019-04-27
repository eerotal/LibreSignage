<?php
/*
* Logging functions for LibreSignage.
*/


// Define log destinations.
define('LOGDEF', LOG_DIR."/default.log");
define('LOGERR', LOG_DIR."/error.log");

$LS_LOG_ENABLED = FALSE;

function ls_log_enable(bool $state): void {
	/*
	*  Enable/disable logging.
	*/
	global $LS_LOG_ENABLED;
	$LS_LOG_ENABLED = $state;
}

function ls_log_open(string $log, int $lock, string $mode) {
	/*
	*  Open and lock a log file.
	*/
	$handle = fopen($log, $mode);
	if ($handle === FALSE) {
		throw new IntException("Failed to create log file '$log'.");
	}
	if (flock($handle, $lock) === FALSE) {
		throw new IntException("flock('$log', ...) failed.");
	}
	return $handle;
}

function ls_log_close($handle): void {
	/*
	*  Release and close a log file.
	*/
	assert(
		is_resource($handle),
		'$resource must be a resource.'
	);
	assert(
		get_resource_type($handle) ==='stream',
		'$resource must be an open file handle'
	);

	if (flock($handle, LOCK_UN) === FALSE) {
		throw new IntException("flock('$log', LOCK_UN) failed.");
	}
	if (fclose($handle) === FALSE) {
		throw new IntException("fclose() on '$log' failed.");
	}
}

function ls_log_truncate(int $len, string $log): void {
	/*
	*  Truncate a log file to $len lines by deleting lines
	*  starting from the beginning of the file.
	*/
	$ln = '';
	$lines = [];

	// Read old log contents.
	$handle = ls_log_open($log, LOCK_EX, 'r');
	while (($ln = fgets($handle)) !== FALSE) { $lines[] = $ln; }
	ls_log_close($handle);

	// Write truncated contents.
	if (count($lines) <= $len) { return; }
	$handle = ls_log_open($log, LOCK_EX, 'w');
	for ($i = count($lines) - $len; $i < count($lines); $i++) {
		fwrite($handle, $lines[$i]);
	}
	ls_log_close($handle);
}

function ls_log(string $msg, string $log = LOGDEF): void {
	/*
	*  Log a message into one of the log files.
	*/
	global $LS_LOG_ENABLED;
	if (!$LS_LOG_ENABLED) { return; }

	if (!is_dir(dirname($log))) {
		if (!mkdir(dirname($log))) {
			throw new IntException('Failed to create log directory.');
		}
	}
	$handle = ls_log_open($log, LOCK_EX, 'a');

	$bt = debug_backtrace();
	if (count($bt) !== 0) { $file = $bt[0]['file']; }

	if (fwrite($handle, "[$file] [".date('r')."] ".$msg."\n") === FALSE) {
		throw new IntException("fwrite() on '$log' failed.");
	}
	ls_log_close($handle);
	ls_log_truncate(LOG_MAX_LEN, $log);
}
