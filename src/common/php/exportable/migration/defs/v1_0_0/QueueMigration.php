<?php

namespace libresignage\common\php\exportable\migration\defs\v1_0_0;

use libresignage\common\php\Util;
use libresignage\common\php\Config;
use libresignage\common\php\exportable\migration\MigrationInterface;
use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\exportable\ExportableDataContext;
use libresignage\common\php\exportable\migration\exceptions\MigrationException;

/**
* Queue data migration from 1.0.0 to 1.1.0.
*/
final class QueueMigration implements MigrationInterface {
	public static function from_class(): array {
		return [
			'libresignage\common\php\queue\Queue',
			'Queue'
		];
	}

	public static function to_class(): string {
		return 'libresignage\common\php\queue\Queue';
	}

	public static function from_version(): array {
		/*
		* Use the fallback 0.0.0 because v1.0.0
		* didn't include versions in data.
		*/
		return ['0.0.0'];
	}

	public static function to_version(): string {
		return '1.1.0';
	}

	public static function migrate(array &$data, ExportableDataContext $ctx) {
		// rename: slide_ids -> slides
		$data['slide_ids'] = $data['slides'];
		unset($data['slides']);

		// new: name, infer from filepath.
		if ($ctx->has(ExportableDataContext::FILEPATH)) {
			$tmp = $ctx->get(ExportableDataContext::FILEPATH);
			$data['name'] = pathinfo($tmp, PATHINFO_FILENAME);
		} else {
			throw new MigrationException(
				"Queue migration requires filepath in context object."
			);
		}

		// Add the private visibility field that was previously missing.
		$data[Exportable::EXP_VISIBILITY] = Exportable::EXP_VISIBILITY_PRIVATE;
	}
}
