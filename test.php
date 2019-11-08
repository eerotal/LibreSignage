<?php

$_SERVER['DOCUMENT_ROOT'] = '/home/eero/LibreSignage/dist/public';
require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\common\php\Config;

/*use libresignage\common\php\exportable\ExportableTransformationPath;

$a = [
	'__classname' => 'libresignage\\common\\php\\queue\\Queue',
	'__visibility' => 'private',
	'version' => '1.0.0',
	'slides' => ['aa', 'bb']
];
$p = new ExportableTransformationPath(
	$a,
	'1.1.0'	
);

var_dump($p->transform());*/


use libresignage\common\php\exportable\migration\MigrationIndex;
MigrationIndex::write(
	Config::config('LIBRESIGNAGE_ROOT').'/common/php/exportable/migration/index.json',
	Config::config('LIBRESIGNAGE_ROOT').'/common/php/exportable/migration/defs'
);


/*$i->load(Config::config('LIBRESIGNAGE_ROOT')."/common/php/exportable/transformations/index.json");
$fqcn = $i->get('1.0.0')->get_fqcn();
$t = new $fqcn();
echo $t->classname();*/

//use libresignage\common\php\exportable\transformations\TransformationIndexEntry;
//$e = new TransformationIndexEntry('1.1.*', '2.2.0', 'aaa/aaa/bbb');
//var_dump($e->transforms('0.1.10'));
