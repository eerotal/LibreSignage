<?php

namespace common\php;

use common\php\ErrorHandler;

/**
* Setup functions for LibreSignage.
*/
final class Config {
	const CONFIG_DIR  = "config/conf";
	const QUOTA_DIR   = "config/quota";
	const LIMITS_DIR  = "config/limits";

	/**
	* Setup LibreSignage.
	*
	* @throws Exception if config values are problematic.
	*/
	public static function setup() {
		define('LIBRESIGNAGE_ROOT', $_SERVER['DOCUMENT_ROOT'].'/..');
		require_once(LIBRESIGNAGE_ROOT.'/vendor/autoload.php');		

		// Load LibreSignage config.
		self::define_array_values(
			self::load_config_array(
				LIBRESIGNAGE_ROOT.'/'.self::CONFIG_DIR
			)
		);
		define(
			'LS_LIMITS',
			self::load_config_array(LIBRESIGNAGE_ROOT.'/'.self::LIMITS_DIR)
		);
		define(
			'LS_QUOTAS',
			self::load_config_array(LIBRESIGNAGE_ROOT.'/'.self::QUOTA_DIR)
		);

		// Do some checks on the configured values.
		$max_slides = LS_QUOTAS['slides']['limit']*self::limit('MAX_USERS');
		if ($max_slides > self::limit('SLIDE_MAX_INDEX') - 1) {
			throw new \Exception(
				'The configured slide quota conflicts with the '.
				'configured maximum slide index value.'
			);
		}

		ErrorHandler::setup();
		ErrorHandler::set_debug(LIBRESIGNAGE_DEBUG);
	}

	/**
	* Load all of the config files in $dir in ascending
	* alphabetical order. The config files must return
	* an associative array of config values. This function
	* returns the final config data after all of the individual
	* files have been processed.
	*
	* @param string $dir The directory where the config files are.
	* @return array The configuration array.
	* @throws Exception if a config file doesn't return an array.
	*/
	private static function load_config_array(string $dir): array {
		$tmp = [];
		$files = scandir($dir, SCANDIR_SORT_ASCENDING);
		if ($files !== FALSE) {
			foreach (array_diff($files, ['.', '..']) as $f) {
				$inc = include($dir.'/'.$f);
				if (gettype($inc) === 'array') {
					$tmp = array_merge($tmp, $inc);
				} else {
					throw new \Exception(
						"Invalid configuration file. An array wasn't returned."
					);
				}
			}
		}
		return $tmp;
	}

	/**
	* Define the value in $arr as global constants.
	*
	* @param array $arr The constants array.
	*/
	private static function define_array_values(array $arr) {
		foreach ($arr as $k => $v) {
			if (gettype($k) === 'string') {
				define($k, $v);
			}
		}
	}

	/**
	* Get the value of a limit.
	*
	* @param string $lim The name of the limit.
	* @return mixed The value of the limit.
	*/
	public static function limit(string $lim) {
		return LS_LIMITS[$lim];
	}
}

/* Bootstrap */
Config::setup();
