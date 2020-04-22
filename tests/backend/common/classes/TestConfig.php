<?php

namespace libresignage\tests\backend\common\classes;

use libresignage\common\php\Config;

/**
* A class for setting up a LibreSignage test environment.
*
* This class is called from PHPUnit's bootstrap.php.
*/
final class TestConfig {
	const LS_DIST_PREFIX = 'dist/';
	const TEST_CONFIG_DIR = 'server/libresignage';

	/**
	* Configure the LibreSignage PHPUnit test framework.
	*/
	public function __construct() {
		echo "[Info] Configuring the unit test framework...\n";

		echo "[Info] Setting up error handling...\n";
		TestConfig::setup_error_handling();

		echo "[Info] Setting up symbol autoloading...\n";
		TestConfig::setup_symbol_autoloading();

		/*
		* Define the LS_TESTING constant to inform LibreSignage
		* that tests are running. The value of this constant
		* can be read from within LibreSignage by calling
		* libresignage\common\php\Config::is_testing().
		*/
		define('LS_TESTING', TRUE);

		/*
		* Read config from the temporary config path in the source
		* tree while testing. This is done because config files are
		* only copied to the LibreSignage distribution when 'make install'
		* is run.
		*/
		$conf_path = self::get_src_root().'/'.self::TEST_CONFIG_DIR;
		echo "[Info] Adding '$conf_path' as a configuration load path...\n";
		\libresignage\common\php\Config::add_config_load_path($conf_path);
		echo "[Info] Reloading the LibreSignage config...\n";
		\libresignage\common\php\Config::load_config();
	}

	/**
	* Setup global error handling.
	*/
	public static function setup_error_handling() {
		// Convert PHP errors to exceptions.
		\set_error_handler(function(int $severity, string $msg, string $file, int $line) {
			if (!(\error_reporting() & $severity)) { return false; }
			throw new \ErrorException($msg, 0, $severity, $file, $line);
		});
	}

	/**
	* Setup symbol autoloading to work with the test framework.
	*/
	public static function setup_symbol_autoloading() {
		$autoloader = require(
			join(
				'/',
				[
					self::get_src_root(),
					'vendor',
					'autoload.php'
				]
			)
		);

		/*
		* Prefix autoload paths from composer's autoload.php with
		* LS_DIST_PREFIX to make path resolution work in the dev tree.
		*/
		echo "[Info] Applying prefixes to production autoload paths...\n";
		echo "[Info] Make sure your '".self::LS_DIST_PREFIX.
			"' is up-to-date!\n";

		$base = self::get_src_root();
		foreach ($autoloader->getPrefixesPsr4() as $namespace => $paths) {
			foreach ($paths as &$p) {
				/*
				* Path doesn't exist as-is, prefix it with LS_DIST_PREFIX.
				* This check makes sure dev dep paths not copied into dist/
				* are not modified.
				*/
				if (strlen(realpath($p)) === 0) {
					$p = \preg_replace(
						':^'.$base.':',
						realpath($base.'/'.TestConfig::LS_DIST_PREFIX),
						$p
					);
					if ($p === NULL) {
						throw new \Exception("preg_match() failed.");
					} else if (strlen(realpath($p)) === 0) {
						throw new \Exception("Namespace path doesn't exist. ($p)");
					}
				}
			}
			$autoloader->setPsr4($namespace, $paths);
		}
	}

	/**
	* Get the path to the LibreSignage source root.
	*
	* @return string A path to the source root.
	*/
	private static function get_src_root(): string {
		$ret = realpath(__DIR__.'/../../../../');
		if ($ret) {
			return $ret;
		} else {
			throw new \Exception('Failed to construct source root path.');
		}
	}
}
