<?php

namespace libresignage\common\php\exportable\migration\defs\v1_1_0;

use libresignage\common\php\Util;
use libresignage\common\php\Config;
use libresignage\common\php\exportable\migration\MigrationInterface;
use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\exportable\ExportableDataContext;
use libresignage\common\php\exportable\migration\exceptions\MigrationException;

/**
* Queue data migration from 1.1.0 to 1.2.0.
*/
final class QueueMigration implements MigrationInterface {
	public static function from_class(): array {
		return ['libresignage\common\php\queue\Queue'];
	}

	public static function to_class(): string {
		return 'libresignage\common\php\queue\Queue';
	}

	public static function from_version(): array {
		return ['1.1.0'];
	}

	public static function to_version(): string {
		return '1.2.0';
	}

	public static function migrate(array &$data, ExportableDataContext $ctx) {}
}
