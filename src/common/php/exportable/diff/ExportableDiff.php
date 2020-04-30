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
class ExportableDiff extends BaseDiff {
	protected $base = NULL;
	protected $other = NULL;

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
				in_array($k, $this->base::__exportable_private())
			);
		}
	}

	public function is_equal(bool $compare_private): bool {
		if ($this->is_private() && !$compare_private) { return TRUE; }

		foreach ($this->diff as $v) {
			if (!$v->is_equal($compare_private)) { return FALSE; }
		}

		return TRUE;
	}

	public function dump(bool $compare_private): array {
		$ret = [];
		foreach ($this->diff as $k => $v) {
			$ret = array_merge(
				$ret,
				[BaseDiff::COLOR_DEFAULT.$k.':'],
				BaseDiff::indent_dump_str_array($v->dump($compare_private), 1),
			);
		}
		return $ret;
	}
}
