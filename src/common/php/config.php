<?php
	/*
	*  LibreSignage config definitions.
	*/

	define("LIBRESIGNAGE_ROOT", $_SERVER['DOCUMENT_ROOT'].'/..');

	// Require the composer autoload file.
	require_once(LIBRESIGNAGE_ROOT.'/vendor/autoload.php');

	const CONFIG_DIR  = "config/conf";
	const QUOTA_DIR   = "config/quota";
	const LIMITS_DIR  = "config/limits";

	function load_config_array(string $dir) {
		/*
		*  Load all of the config files in $dir in ascending
		*  alphabetical order. The config files must return
		*  an associative array of config values. This function
		*  returns the final config data after all of the individual
		*  files have been processed.
		*/
		$tmp = [];
		$files = scandir($dir, SCANDIR_SORT_ASCENDING);
		if ($files !== FALSE) {
			foreach (array_diff($files, ['.', '..']) as $f) {
				$inc = include($dir.'/'.$f);
				if (gettype($inc) === 'array') {
					$tmp = array_merge($tmp, $inc);
				} else {
					throw new Exception(
						"Invalid configuration file. An array wasn't returned."
					);
				}
			}
		}
		return $tmp;
	}

	function define_array_values(array $arr) {
		foreach ($arr as $k => $v) {
			if (gettype($k) === 'string') {
				define($k, $v);
			}
		}
	}

	function gtlim(string $lim) {
		// Get the value of a limit.
		return LS_LIMITS[$lim];
	}
	// Load LibreSignage configuration files.
	define_array_values(load_config_array(
		LIBRESIGNAGE_ROOT.'/'.CONFIG_DIR
	));
	define('LS_LIMITS', load_config_array(
		LIBRESIGNAGE_ROOT.'/'.LIMITS_DIR
	));
	define('LS_QUOTAS', load_config_array(
		LIBRESIGNAGE_ROOT.'/'.QUOTA_DIR
	));

	// Do some checks on the configured values.
	$max_slides = LS_QUOTAS['slides']['limit']*gtlim('MAX_USERS');
	if ($max_slides > gtlim('SLIDE_MAX_INDEX') - 1) {
		throw new Exception(
			'The configured slide quota conflicts with the '.
			'configured maximum slide index value.'
		);
	}
	unset($max_slides);

	// Load error definitions & functions.
	require_once(LIBRESIGNAGE_ROOT.'/common/php/error.php');
	error_setup();
	error_set_debug(LIBRESIGNAGE_DEBUG);
