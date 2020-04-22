<?php

namespace libresignage\common\php;

use libresignage\common\php\ErrorHandler;
use libresignage\common\php\exceptions\ConfigException;

/**
* Setup functions for LibreSignage.
*/
final class Config {
	const CONFIG_DIR  = "conf/";
	const QUOTA_DIR   = "quota/";
	const LIMITS_DIR  = "limits/";

	private static $config_load_paths = [];
	private static $limits = [];
	private static $quotas = [];
	private static $config = [];

	/**
	* Setup LibreSignage.
	*
	* @throws Exception if config values are problematic.
	*/
	public static function setup() {
		$root = dirname(__FILE__).'/../..';
		/*
		* Only include the Composer autoload file if tests are not running
		* because the test framework includes the file automatically.
		*/
		if (!self::is_testing()) {
			require_once($root.'/vendor/autoload.php');
		}

		self::add_config_load_path(join('/', [$root, 'config']));
		self::load_config();

		// Do some checks on the configured values.
		$max_slides = self::quota('slides')['limit']*self::limit('MAX_USERS');
		if ($max_slides > self::limit('SLIDE_MAX_INDEX') - 1) {
			throw new \Exception(
				'The configured slide quota conflicts with the '.
				'configured maximum slide index value.'
			);
		}

		/*
		* Only set up error handling if test are not running because
		* the test framework has its own way of handling errors.
		*/
		if (!self::is_testing()) {
			ErrorHandler::setup();
			ErrorHandler::set_debug(self::config('LIBRESIGNAGE_DEBUG'));
		}
	}

	/**
	* Add a path to the array of config load paths.
	*
	* @param string $path A new config load path.
	*
	* @throws ConfigException If $path doesn't exist.
	*/
	public static function add_config_load_path(string $path) {
		if (!realpath($path)) {
			throw new ConfigException(
				"Config load path '$path' doesn't exist."
			);
		}
		self::$config_load_paths[] = $path;
	}

	/**
	* Load the LibreSignage config files.
	*
	* If some configuration values are already loaded, new values
	* will override the previous ones.
	*/
	public static function load_config() {
		foreach (self::$config_load_paths as $p) {
			self::$config = array_merge(
				self::$config,
				self::load_config_array(join('/', [$p, self::CONFIG_DIR])),
				['LIBRESIGNAGE_ROOT' => dirname(__FILE__).'/../..']
			);
			self::$limits = array_merge(
				self::$limits,
				self::load_config_array(join('/', [$p, self::LIMITS_DIR]))
			);
			self::$quotas = array_merge(
				self::$quotas,
				self::load_config_array(join('/', [$p, self::QUOTA_DIR]))
			);
		}
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
		if (!is_dir($dir)) { return []; }
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
	* Get a quota.
	*
	* @param string $quota The name of the quota.
	*
	* @return array The matching quota data.
	*/
	public static function quota(string $quota): array {
		return self::$quotas[$quota];
	}

	/**
	* Get a limit.
	*
	* @param string $lim The name of the limit.
	*
	* @return mixed The value of the limit.
	*/
	public static function limit(string $lim) {
		return self::$limits[$lim];
	}

	/**
	* Get a config value.
	*
	* @param string $conf The name of the config value.
	*
	* @return mixed The config value.
	*/
	public static function config(string $conf) {
		return self::$config[$conf];
	}

	public static function get_quotas(): array { return self::$quotas; }
	public static function get_limits(): array { return self::$limits; }
	public static function get_config(): array { return self::$config; }

	/**
	* Check whether LibreSignage unit tests are running.
	*
	* @return bool TRUE if tests are running and FALSE otherwise.
	*/
	public static function is_testing(): bool {
		return defined('LS_TESTING') && LS_TESTING;
	}
}

/* Bootstrap */
Config::setup();
