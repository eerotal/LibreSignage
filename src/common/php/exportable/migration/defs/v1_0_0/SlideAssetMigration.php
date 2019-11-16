<?php

namespace libresignage\common\php\exportable\migration\defs\v1_0_0;

use libresignage\common\php\Util;
use libresignage\common\php\Config;
use libresignage\common\php\exportable\migration\MigrationInterface;
use libresignage\common\php\exportable\ExportableDataContext;

/**
* SlideAsset data migration from 1.0.0 to 1.1.0.
*/
final class SlideAssetMigration implements MigrationInterface {
	public static function from_class(): array {
		return [
			'libresignage\common\php\slide\SlideAsset',
			'SlideAsset'
		];
	}

	public static function to_class(): string {
		return 'libresignage\common\php\slide\SlideAsset';
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
		// new: slide_id, infer from 'fullpath'
		$tmp = explode("/", $data['fullpath']);
		$data['slide_id'] = $tmp[count($tmp) - 3];

		// new: hash, calculate from file.
		$data['hash'] = md5_file($data['fullpath']);

		// remove: fullpath, intname, thumbname, thumbpath
		unset($data["fullpath"]);
		unset($data["intname"]);
		unset($data["thumbname"]);
		unset($data["thumbpath"]);
	}
}
