<?php

namespace libresignage\common\php\exportable;

use libresignage\common\php\exportable\BaseDiff;

final class PrimitiveDiff extends BaseDiff {
	private $base = NULL;
	private $other = NULL;

	public function __construct($base, $other, bool $private_value) {
		parent::__construct($private_value);

		$this->base = $base;
		$this->other = $other;

		$this->diff = [
			BaseDiff::DIFF_BASE => self::describe_value($this->base),
			BaseDiff::DIFF_OTHER => self::describe_value($this->other),
		];
	}

	public function is_equal(bool $check_private): bool {
		if ($this->is_private() && !$check_private) { return TRUE; }
		return $this->base === $this->other;
	}

	private static function describe_value($value): string {
		if (is_object($value)) {
			return 'object('.get_class($value).')';
		} else if (is_array($value)) {
			return 'array('.'TODO'.')';
		} else {
			return gettype($value).'('.$value.')';
		}
	}

	public function dump(bool $check_private, int $indent): array {
		$ret = [];
		if ($this->is_equal($check_private)) {
			$ret = [BaseDiff::COLOR_DEFAULT.self::describe_value($this->base)];
		} else {
			$ret = [
				BaseDiff::COLOR_BAD.'--- '.self::describe_value($this->base),
				BaseDiff::COLOR_GOOD.'+++ '.self::describe_value($this->other)
			];
		}
		return BaseDiff::indent_str_array($ret, $indent);
	}
}
