<?php

define('UNIT_TEST_ROOT', __DIR__);
define('SCHEMA_PATH', UNIT_TEST_ROOT.'/common/schemas');

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/common/classes/TestConfig.php');
$TEST_CONFIG = new classes\TestConfig();
