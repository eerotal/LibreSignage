<?php

namespace libresignage\common\php\exportable;

use libresignage\common\php\PrimitiveDiff;

abstract class BaseDiff {
	const DIFF_BASE = '__base';
	const DIFF_OTHER = '__other';
	const DIFF_DIFF = '__diff';

	const COLOR_DEFAULT = "\e[49m";
	const COLOR_GOOD = "\e[42m";
	const COLOR_BAD = "\e[41m";

	const INDENT = '    ';

	private $private_value = FALSE;
	private $diff = [];

	public abstract function dump(bool $check_private, int $indent): array;
	public abstract function is_equal(bool $check_private): bool;

	public function __construct(bool $private_value) {
		$this->private_value = $private_value;
	}

	public function is_private(): bool {
		return $this->private_value;
	}

	public function get_diff(): array {
		return $this->diff;
	}

	public static function str_starts_with(string $str, string $prefix) {
		return substr($str, 0, strlen($prefix)) == $prefix;
	}

	public static function indent_str_array(array $arr, int $level): array {
		$ret = [];
		$color = '';

		foreach ($arr as $str) {
			if (self::str_starts_with($str, self::COLOR_GOOD)) {
				$color = self::COLOR_GOOD;
			} else if (self::str_starts_with($str, self::COLOR_BAD)) {
				$color = self::COLOR_BAD;
			} else if (self::str_starts_with($str, self::COLOR_DEFAULT)) {
				$color = self::COLOR_DEFAULT;
			}

			$str = substr($str, strlen($color));
			$ret[] = $color.str_repeat(self::INDENT, $level).$str;
		}
		return $ret;
	}

	public function dump_str(bool $check_private, int $indent = 0): string {
		$ret = '';
		$dump = $this->dump($check_private, $indent);
		foreach ($dump as $ln) {
			$ret .= $ln."\n";
		}
		return $ret;
	}
}
