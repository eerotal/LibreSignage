<?php

namespace libresignage\common\php\exportable\diff;

use libresignage\common\php\exportable\diff\Diff;
use libresignage\common\php\exportable\diff\BaseDiff;
use libresignage\common\php\exportable\diff\PrimitiveDiff;
use libresignage\common\php\exportable\diff\ObjectDiff;
use libresignage\common\php\exportable\diff\ArrayDiff;
use libresignage\common\php\exportable\diff\ExportableDiff;

/**
* A class that describes a diff between two arrays.
*/
class ArrayDiff extends BaseDiff {
	protected $base = [];
	protected $other = [];

	public function __construct(
		array $base,
		$other,
		int $depth,
		bool $private
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
	protected function diff(int $depth) {
		foreach($this->base as $base_key => $base_value) {
			if (array_key_exists($base_key, $this->other)) {
				$other_value = $this->other[$base_key];
			} else {
				$other_value = NULL;
			}

			if ($depth > 0) { $depth--; }

			$this->diff[$base_key] = Diff::diff(
				$base_value,
				$other_value,
				$depth,
				FALSE
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
				[BaseDiff::COLOR_DEFAULT."$k:"],
				BaseDiff::indent_dump_str_array($v->dump($compare_private), 1)
			);
		}
		return $ret;
	}
}
