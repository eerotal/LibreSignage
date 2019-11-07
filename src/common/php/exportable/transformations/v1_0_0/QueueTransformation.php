<?php

namespace libresignage\common\php\exportable\transformations\v1_0_0;

use libresignage\common\php\Util;
use libresignage\common\php\Config;
use libresignage\common\php\exportable\transformations\TransformationInterface;

/**
* Queue data transformation from 1.0.0 to 1.1.0.
*/
final class QueueTransformation implements TransformationInterface {
	public static function classname(): string {
		return 'libresignage\common\php\queue\Queue';
	}

	public static function from_version(): string {
		/*
		* Use the fallback 0.0.0 because v1.0.0
		* didn't include versions in data.
		*/
		return '0.0.0';
	}

	public static function to_version(): string {
		return '1.1.0';
	}

	public static function transform(array &$data) {
		// rename: slide_ids -> slides
		$data['slide_ids'] = $data['slides'];
		unset($data['slides']);

		// new: name
		$data['name'] = Util::get_uid(Config::limit('QUEUE_NAME_MAX_LEN'));
	}
}
