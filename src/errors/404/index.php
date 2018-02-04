<?php
	/*
	*  Build time flags.
	*    !!BUILD_VERIFY_NOCONFIG!!
	*/
	$ERROR_PAGE_HEADING = '404 Not Found';
	$ERROR_PAGE_TEXT = "The page you are trying to access doesn't ".
				"seem to exist.";
	include($_SERVER['DOCUMENT_ROOT'].'/errors/error.php');
