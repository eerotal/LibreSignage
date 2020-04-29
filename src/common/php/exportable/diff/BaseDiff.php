<?php

namespace libresignage\common\php\exportable\diff;

use libresignage\common\php\Util;

abstract class BaseDiff {
	const DIFF_DEPTH_INF = -1;

	const DIFF_BASE = '__base';
	const DIFF_OTHER = '__other';

	const COLOR_DEFAULT = "\e[49m";
	const COLOR_GOOD = "\e[42m";
	const COLOR_BAD = "\e[41m";

	const INDENT = '    ';

	private $priv = FALSE;
	protected $diff = [];

	/**
	* Construct a new diff object.
	*
	* @param bool $private Set this to TRUE if the created diff object
	*                      describes a diff between two private Exportable
	*                      members. Otherwise this should be FALSE.
	*/
	public function __construct(bool $private) {
		$this->priv = $private;
	}

	/**
	* Get a dump of a diff as an array.
	*
	* @param bool $compare_private If TRUE, private Exportable values are also
	*                              compared in the dump. Otherwise private values
	*                              are included in the dump but they are always
	*                              considered equal.
	* @param int $indent           The first indentation level.
	*/
	public abstract function dump(bool $compare_private, int $indent): array;

	/**
	* Check whether the two objects used for a diff are equal.
	*
	* @param bool $compare_private If TRUE, private Exportable values are also
	*                              compared. Otherwise private values are always
	*                              considered equal.
	*/
	public abstract function is_equal(bool $compare_private): bool;

	/**
	* Check whether a diff is between private Exportable members.
	*
	* @return bool
	*/
	public function is_private(): bool { return $this->priv; }

	/**
	* Get the internal diff array.
	*
	* @return array
	*/
	public function get_diff(): array { return $this->diff; }

	/**
	* Indent a dump string array.
	*
	* This method preserves any color formatting codes at the beginning
	* of lines in the array.
	*
	* @param array $arr The array to indent.
	* @param int $level The indentation level.
	*
	* @return array The indented array.
	*/
	public static function indent_dump_str_array(array $arr, int $level): array {
		$ret = [];
		$color = '';

		foreach ($arr as $str) {
			if (Util::str_starts_with($str, self::COLOR_GOOD)) {
				$color = self::COLOR_GOOD;
			} else if (Util::str_starts_with($str, self::COLOR_BAD)) {
				$color = self::COLOR_BAD;
			} else if (Util::str_starts_with($str, self::COLOR_DEFAULT)) {
				$color = self::COLOR_DEFAULT;
			}

			$str = substr($str, strlen($color));
			$ret[] = $color.str_repeat(self::INDENT, $level).$str;
		}
		return $ret;
	}

	/**
	* Dump a diff as a string.
	*
	* @return string The diff dump as a string.
	*/
	public function dump_str(bool $compare_private, int $indent = 0): string {
		$ret = '';
		$dump = $this->dump($compare_private, $indent);
		foreach ($dump as $ln) {
			$ret .= $ln."\n";
		}
		return $ret;
	}
}
