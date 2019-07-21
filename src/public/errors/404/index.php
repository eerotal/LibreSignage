<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');
	use libresignage\common\php\Config;

	$ERROR_PAGE_HEADING = '404 Not Found';
	$ERROR_PAGE_TEXT = "The page you are trying to access doesn't seem to exist.";

	include(Config::config('LIBRESIGNAGE_ROOT').'/errors/error.php');
