<?php
/*
*  Send a 404 if a user tries to directly access a file where
*  this file is included.
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/error.php');
if (count(get_included_files()) <= 4) {
	error_handle(404);
}
