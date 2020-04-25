<?php

namespace libresignage\common\php\exportable;

use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\exportable\BaseDiff;

final class ExportableDiff extends BaseDiff {
	private $base = NULL;
	private $other = NULL;
	private $depth = 0;

	const DIFF_CLASSNAME = '__classname';
	const DIFF_DEPTH_INF = -1;

	public function __construct(
		Exportable $base,
		Exportable $other,
		int $depth,
		bool $private_value = FALSE
	) {
		parent::__construct($private_value);

		$this->base = $base;
		$this->other = $other;
		$this->depth = $depth;

		$this->diff();
	}

	private function diff() {
		$keys = array_merge(
			$this->base::__exportable_public(),
			$this->base::__exportable_private()
		);

		$this->diff[self::DIFF_CLASSNAME] = new PrimitiveDiff(
			get_class($this->base),
			get_class($this->other),
			FALSE
		);

		foreach ($keys as $k) {
			$base_val = $this->base->__exportable_get($k);
			$other_val = $this->other->__exportable_get($k);

			if (is_subclass_of($base_val, Exportable::class)) {
				$new_depth = $this->depth;
				if ($new_depth > 0) { $new_depth--; }

				$this->diff[$k] = new ExportableDiff(
					$base_val,
					$other_val,
					$new_depth,
					array_key_exists($k, $this->base::__exportable_private())
				);
			} else {
				$this->diff[$k] = new PrimitiveDiff(
					$base_val,
					$other_val,
					array_key_exists($k, $this->base::__exportable_private())
				);
			}
		}
	}

	public function is_equal(bool $check_private): bool {
		if ($this->is_private() && !$check_private) { return TRUE; }
		foreach ($this->diff as $v) {
			if ($v->is_private() && !$check_private) {
				continue;
			} else if (!$v->is_equal($check_private)) {
				return FALSE;
			}
		}
		return TRUE;
	}

	public function dump(bool $check_private, int $indent = 0): array {
		$ret = [];
		foreach ($this->diff as $k => $v) {
			$ret = array_merge(
				$ret,
				[BaseDiff::COLOR_DEFAULT.$k.':'],
				$v->dump($check_private, $indent + 1),
			);
		}
		return BaseDiff::indent_str_array($ret, $indent);
	}
}
