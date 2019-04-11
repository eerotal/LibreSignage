<?php
/*
*  Send a 404 if a user tries to directly access a file where
*  this file is included.
*/
require_once(LIBRESIGNAGE_ROOT.'/common/php/config.php');

// HACK: Move secret PHP files to a folder outside of the web root.
if (count(get_included_files()) <= 4) {
	error_handle(404);
}
