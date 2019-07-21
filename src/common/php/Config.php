<?php

namespace libresignage\common\php;

use libresignage\common\php\ErrorHandler;
use libresignage\common\php\exceptions\ConfigException;

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
		$tmp = $_SERVER['DOCUMENT_ROOT'].'/..';
		require($tmp.'/vendor/autoload.php');

		define(
			'LS_CONFIG',
			array_merge(
				self::load_config_array($tmp.'/'.self::CONFIG_DIR),
				['LIBRESIGNAGE_ROOT' => $tmp]
			)
		);
		define(
			'LS_LIMITS',
			self::load_config_array($tmp.'/'.self::LIMITS_DIR)
		);
		define(
			'LS_QUOTAS',
			self::load_config_array($tmp.'/'.self::QUOTA_DIR)
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
		ErrorHandler::set_debug(self::config('LIBRESIGNAGE_DEBUG'));
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
	* Get a quota.
	*
	* @param string $quota The name of the quota.
	*
	* @return array The matching quota data.
	*/
	public static function quota(string $quota): array {
		return LS_QUOTAS[$quota];
	}

	/**
	* Get a limit.
	*
	* @param string $lim The name of the limit.
	*
	* @return mixed The value of the limit.
	*/
	public static function limit(string $lim) {
		return LS_LIMITS[$lim];
	}

	/**
	* Get a config value.
	*
	* @param string $conf The name of the config value.
	*
	* @return mixed The config value.
	*/
	public static function config(string $conf) {
		return LS_CONFIG[$conf];
	}

	public static function get_quotas(): array { return LS_QUOTAS; }
	public static function get_limits(): array { return LS_LIMITS; }
	public static function get_config(): array { return LS_CONFIG; }
}

/* Bootstrap */
Config::setup();
