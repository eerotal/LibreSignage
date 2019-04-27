<?php
	$ERROR_PAGE_HEADING = '404 Not Found';
	$ERROR_PAGE_TEXT = "The page you are trying to access doesn't ".
				"seem to exist.";
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
	include(LIBRESIGNAGE_ROOT.'/errors/error.php');
