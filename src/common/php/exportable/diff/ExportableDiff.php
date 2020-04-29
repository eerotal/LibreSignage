<?php

namespace libresignage\common\php\exportable\diff;

use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\exportable\diff\PrimitiveDiff;
use libresignage\common\php\exportable\diff\BaseDiff;
use libresignage\common\php\exportable\diff\ObjectDiff;
use libresignage\common\php\exportable\diff\ArrayDiff;

/**
* A class that describes a diff between two Exportable objects.
*/
final class ExportableDiff extends BaseDiff {
	private $base = NULL;
	private $other = NULL;

	const DIFF_CLASSNAME = '__classname';

	public function __construct(
		Exportable $base,
		Exportable $other,
		int $depth,
		bool $private = FALSE
	) {
		parent::__construct($private);

		$this->base = $base;
		$this->other = $other;

		$this->diff($depth);
	}

	/**
	* Create a diff.
	*
	* @param int $depth The maximum recursion depth for the diff.
	*/
	private function diff(int $depth) {
		$this->diff[self::DIFF_CLASSNAME] = new PrimitiveDiff(
			get_class($this->base),
			get_class($this->other),
			FALSE
		);

		$keys = array_merge(
			$this->base::__exportable_public(),
			$this->base::__exportable_private()
		);

		foreach ($keys as $k) {
			$base_val = $this->base->__exportable_get($k);
			$other_val = $this->other->__exportable_get($k);

			if ($depth > 0) { $depth--; }

			$this->diff[$k] = Diff::diff(
				$base_val,
				$other_val,
				$depth,
				array_key_exists($k, $this->base::__exportable_private())
			);
		}
	}

	public function is_equal(bool $compare_private): bool {
		if ($this->is_private() && !$compare_private) { return TRUE; }

		foreach ($this->diff as $v) {
			if ($v->is_private() && !$compare_private) {
				continue;
			} else if (!$v->is_equal($compare_private)) {
				return FALSE;
			}
		}

		return TRUE;
	}

	public function dump(bool $compare_private, int $indent = 0): array {
		$ret = [];
		foreach ($this->diff as $k => $v) {
			$ret = array_merge(
				$ret,
				[BaseDiff::COLOR_DEFAULT.$k.':'],
				$v->dump($compare_private, $indent + 1),
			);
		}
		return BaseDiff::indent_dump_str_array($ret, $indent);
	}
}
