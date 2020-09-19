<?php

/**
* Write an Exportable migration index file as a part of the build process.
*
* This script uses the LibreSignage build from dist/ so you must call
* this script late in the build process.
*/

// Setup DOCUMENT_ROOT to point to 'dist/public/'.
$_SERVER['DOCUMENT_ROOT'] = getcwd().'/dist/public';
require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\common\php\Config;
use libresignage\common\php\exportable\migration\MigrationIndex;

// Write the migration index.
echo "Generating Exportable migration index...\n";
$num = MigrationIndex::write(
	Config::config('LIBRESIGNAGE_ROOT').'/common/php/exportable/migration/index.json',
	Config::config('LIBRESIGNAGE_ROOT').'/common/php/exportable/migration/defs'
);
echo "Wrote an index with $num classes.\n";
