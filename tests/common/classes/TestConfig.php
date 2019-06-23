<?php

namespace classes;

final class TestConfig {
	const LS_DIST_PREFIX = 'dist/';
	const INCLUDE_PATHS = [
		__DIR__.'/..'
	];

	public function __construct() {
		echo "[Info] Configuring the unit test framework...\n";
		TestConfig::setup_error_handling();
		TestConfig::setup_symbol_autoloading();
	}

	public static function setup_error_handling() {
		// Convert PHP errors to exceptions.
		\set_error_handler(function(int $severity, string $msg, string $file, int $line) {
			if (!(\error_reporting() & $severity)) { return false; }
			throw new \ErrorException($msg, 0, $severity, $file, $line);
		});
	}

	public static function autoload_symbol(string $name) {
		$tmp = str_replace('\\', '/', $name);
		foreach (TestConfig::INCLUDE_PATHS as $i) {
			try {
				include(implode('/', [$i, $tmp.'.php']));
			} catch (\Exception $e) { continue; }
			break;
		}
	}

	public static function setup_symbol_autoloading() {
		$autoloader = require(__DIR__.'/../../../vendor/autoload.php');

		/*
		* Prefix autoload paths from composer's autoload.php with
		* LS_DIST_PREFIX to make path resolution work in the dev tree.
		*/
		echo "[Info] Applying prefixes to autoload paths...\n";
		echo "[Info] Make sure your 'dist/' is up-to-date!\n\n";

		$base = realpath(__DIR__.'/../../../');
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

		\spl_autoload_register(['classes\TestConfig', 'autoload_symbol']);
	}
}
