<?php
	$ERROR_PAGE_HEADING = '404 Not Found';
	$ERROR_PAGE_TEXT = "The page you are trying to access doesn't ".
				"seem to exist.";
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');
	include(Config::config('LIBRESIGNAGE_ROOT').'/errors/error.php');
