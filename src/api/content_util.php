<?php
	/*
	*  API utility functions.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');

	function get_content_uri_list() {
		$content_abs_dir = realpath(LIBRESIGNAGE_ROOT.'/'.CONTENT_DIR);
		$content_files = scandir($content_abs_dir);
		$content_files = array_values(array_diff($content_files,
						array('.', '..')));
		$i = 0;
		while ($i < count($content_files)) {
			if (substr($content_files[$i], 0, 1) == ".") {
				array_splice($content_files, $i, 1);
				continue;
			}
			$content_files[$i] = $content_files[$i];
			$i++;
		}
		return $content_files;
	}

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
		echo json_encode(array('error' => $errcode));
		exit(0);
	}
