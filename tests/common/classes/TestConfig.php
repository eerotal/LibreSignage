<?php

namespace classes;

final class TestConfig {
	const INCLUDE_PATHS = [
		__DIR__.'/..'
	];

	public function __construct() {
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
		\spl_autoload_register(['classes\TestConfig', 'autoload_symbol']);
	}
}
