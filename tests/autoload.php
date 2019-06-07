<?php

require('vendor/autoload.php');

// Convert PHP errors to exceptions.
set_error_handler(function(int $severity, string $msg, string $file, int $line) {
	if (!(error_reporting() & $severity)) { return false; }
	throw new ErrorException($msg, 0, $severity, $file, $line);
});

// Setup class autoloading.
spl_autoload_register(function($class_name) {
	try {
		include($class_name.'.php');
	} catch (Exception $e) {}
});
