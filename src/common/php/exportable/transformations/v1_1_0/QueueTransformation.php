<?php

namespace libresignage\common\php\exportable\transformations\v1_1_0
;

use libresignage\common\php\Util;
use libresignage\common\php\Config;
use libresignage\common\php\exportable\ExportableTransformationInterface;

/**
* Queue data transformation from 1.0.0 to 1.1.0.
*/
final class QueueTransformation implements ExportableTransformationInterface {
	public function classname(): string {
		return 'libresignage\common\php\queue\Queue';
	}

	public function version(): string {
		return '1.0.*';
	}

	public function transform($data) {
		$ret = clone $data;

		// rename: slide_ids -> slides
		$ret['slide_ids'] = $ret['slides'];
		unset($ret['slides']);

		// new: name
		$ret['name'] = Util::get_uid(Config::config('QUEUE_NAME_MAX_LEN'));
	}
}
