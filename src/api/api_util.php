<?php
	/*
	*  API utility functions.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

	function rmdir_recursive($path) {
		/*
		*  Recursively remove a directory.
		*/
		$files = @scandir($path);
		if ($files === FALSE) {
			return FALSE;
		}

		$files = array_diff($files, array('.', '..'));

		foreach ($files as $f) {
			if (@is_dir($path.'/'.$f)) {
				if (!rmdir_recursive($path.'/'.$f)) {
					return FALSE;
				}
			} else {
				if (!@unlink($path.'/'.$f)) {
					return FALSE;
				}
			}
		}
		if (!@rmdir($path)) {
			return FALSE;
		}
		return TRUE;
	}

	function header_plaintext() {
		header('Content-Type: text/plain');
	}

	function error_and_exit($errcode) {
		echo '{"error":'.$errcode.'}';
		exit(0);
	}
