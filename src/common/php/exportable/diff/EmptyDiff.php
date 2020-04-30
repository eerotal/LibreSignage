<?php

namespace libresignage\common\php\exportable\diff;

use libresignage\common\php\exportable\diff\BaseDiff;

/**
* Empty diff class used when the diffing process runs out of
* recursion depth.
*
* This diff class always contains an empty diff and EmptyDiff::is_equal()
* always returns TRUE.
*/
class EmptyDiff extends BaseDiff {
	public function dump(bool $compare_private): array {
		return [];
	}

	public function is_equal(bool $compare_private): bool {
		return TRUE;
	}
}
