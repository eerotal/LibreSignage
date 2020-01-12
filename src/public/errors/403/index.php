<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');
	use libresignage\common\php\Config;

	$ERROR_PAGE_HEADING = '403 Forbidden';
	$ERROR_PAGE_TEXT = 'You are not allowed to access this page.';

	include(Config::config('LIBRESIGNAGE_ROOT').'/errors/error.php');
