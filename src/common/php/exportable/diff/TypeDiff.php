<?php

namespace libresignage\common\php\exportable\diff;

use libresignage\common\php\exportable\diff\BaseDiff;

/**
* A class that describes a diff between types of two values.
*/
class TypeDiff extends BaseDiff {
	protected $base = NULL;
	protected $other = NULL;

	public function __construct($base, $other, bool $private) {
		parent::__construct($private);

		$this->base = $base;
		$this->other = $other;
	}

	private static function describe_type($value): string {
		if (is_object($value)) {
			return 'type(object: '.get_class($value).')';
		} else {
			return 'type('.gettype($value).')';
		}
	}

	public function is_equal(bool $compare_private): bool {
		if ($this->is_private() && !$compare_private) { return TRUE; }

		if (is_object($this->base) && is_object($this->other)){
			return get_class($this->base) === get_class($this->other);
		} else {
			return gettype($this->base) === gettype($this->other);
		}
	}

	public function dump(bool $compare_private): array {
		$ret = [];

		if ($this->is_equal($compare_private)) {
			$ret = [BaseDiff::COLOR_DEFAULT.self::describe_type($this->base)];
		} else {
			$ret = [
				BaseDiff::COLOR_BAD.self::PREFIX_MINUS.self::describe_type($this->base),
				BaseDiff::COLOR_GOOD.self::PREFIX_PLUS.self::describe_type($this->other)
			];
		}

		return $ret;
	}
}
