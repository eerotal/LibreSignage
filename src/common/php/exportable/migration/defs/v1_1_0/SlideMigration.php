<?php

namespace libresignage\common\php\exportable\migration\defs\v1_1_0;

use libresignage\common\php\Util;
use libresignage\common\php\Config;
use libresignage\common\php\exportable\migration\MigrationInterface;
use libresignage\common\php\exportable\ExportableDataContext;
use libresignage\common\php\Log;

/**
* Slide data migration from 1.1.0 to 1.2.0.
*/
final class SlideMigration implements MigrationInterface {
	public static function from_class(): array {
		return ['libresignage\common\php\slide\Slide'];
	}

	public static function to_class(): string {
		return 'libresignage\common\php\slide\Slide';
	}

	public static function from_version(): array { return ['1.1.0']; }
	public static function to_version(): string { return '1.2.0'; }

	public static function migrate(array &$data, ExportableDataContext $ctx) {
		// convert: index <int> -> index <dictionary>
		$tmp = $data['index'];
		$data['index'] = [];
		$data['index'][$data['queue_name']] = $tmp;

		// conbert: queue_name <string> -> queue_names <array>
		$data['queue_names'] = [$data['queue_name']];
		unset($data['queue_name']);
	}
}
