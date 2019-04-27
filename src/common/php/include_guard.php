<?php
/*
*  Send a 404 if a user tries to directly access a file where
*  this file is included.
*/

// HACK: Move secret PHP files to a folder outside of the web root.
if (count(get_included_files()) <= 4) {
	error_handle(404);
}
