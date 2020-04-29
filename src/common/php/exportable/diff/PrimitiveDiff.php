<?php

namespace libresignage\common\php\exportable\diff;

use libresignage\common\php\exportable\diff\BaseDiff;

/**
* A class that describes a diff between two primitive values.
*/
class PrimitiveDiff extends BaseDiff {
	const PREFIX_MINUS = '---';
	const PREFIX_PLUS = '+++';

	const DIFF_BASE = '__base';
	const DIFF_OTHER = '__other';

	protected $base = NULL;
	protected $other = NULL;

	public function __construct($base, $other, bool $private_value) {
		parent::__construct($private_value);

		$this->base = $base;
		$this->other = $other;

		$this->diff = [
			self::DIFF_BASE => $this->base,
			self::DIFF_OTHER => $this->other,
		];
	}

	/**
	* Get a string describing the type and value of a variable.
	*
	* @return string
	*/
	private static function describe_value($value): string {
		return gettype($value).'('.var_export($value, TRUE).')';
	}

	public function is_equal(bool $compare_private): bool {
		if ($this->is_private() && !$compare_private) { return TRUE; }
		return $this->base === $this->other;
	}

	public function dump(bool $compare_private, int $indent): array {
		$ret = [];

		if ($this->is_equal($compare_private)) {
			$ret = [BaseDiff::COLOR_DEFAULT.self::describe_value($this->base)];
		} else {
			$ret = [
				BaseDiff::COLOR_BAD.self::PREFIX_MINUS.self::describe_value($this->base),
				BaseDiff::COLOR_GOOD.self::PREFIX_PLUS.self::describe_value($this->other)
			];
		}

		return BaseDiff::indent_dump_str_array($ret, $indent);
	}
}
