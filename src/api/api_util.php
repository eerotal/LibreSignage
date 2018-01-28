<?php
	/*
	*  API utility functions.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

	/*
	*  Return a stack trace with API errors.
	*  DO NOT set this to TRUE on production systems!
	*/
	$API_ERROR_TRACE = TRUE;

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
		global $API_ERROR_TRACE;
		$err = array(
			'error' => $errcode
		);
		if ($API_ERROR_TRACE) {
			$e = new Exception();
			$err['trace'] = $e->getTraceAsString();
		}

		$err_str = json_encode($err);
		if ($err_str == FALSE &&
			json_last_error() != JSON_ERROR_NONE) {
			echo '{"error": '.API_E_INTERNAL.'}';
			exit(0);
		}
		echo $err_str;
		exit(0);
	}

	function api_get_req_data() {
		$str = @file_get_contents("php://input");
		if ($str === FALSE) {
			throw new Exception('Failed to read '.
					'request data!');
		}
		$data = json_decode($str, $assoc=TRUE);
		if (json_last_error() != JSON_ERROR_NONE) {
			throw new Exception('Request data parsing '.
						'failed!');
		}
		return $data;
	}
