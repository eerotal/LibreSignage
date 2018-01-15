<?php
	/*
	*  UID generator implementation for LibreSignage. The UIDs
	*  generated with get_uid() are guaranteed to be unique in
	*  the context of LibreSignage, since they are successive
	*  hexadecimal integers.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');

	define('UID_MAX_SIZE',	PHP_INT_SIZE);
	define('UID_FILE',	LIBRESIGNAGE_ROOT.'/data/uid.dat');

	function _uid_next(string $current_uid) {
		$tmp = preg_replace('/^0x/', '', $current_uid);
		if (ctype_xdigit($tmp)) {
			return '0x'.dechex(intval($tmp, 16) + 1);
		} else {
			return '0x0';
		}
	}

	function get_uid() {
		/*
		*  Generate a new UID. On success the new UID hex
		*  string is returned. Otherwise an error is thrown.
		*/

		if (!is_dir(dirname(UID_FILE))) {
			if (!@mkdir(dirname(UID_FILE), $mode=0775,
					$recursive=TRUE)) {
				throw new Exception("Failed to create ".
						"data directory!");
			}
		}

		$uid_file = @fopen(UID_FILE, 'c+');
		if ($uid_file === FALSE) {
			throw new Exception("Failed to open UID file!");
		}

		if (flock($uid_file, LOCK_EX)) {
			$uid_f_size = filesize(UID_FILE);
			if ($uid_f_size === FALSE) {
				goto cleanup_and_throw_file_error;
			}

			if ($uid_f_size != 0) {
				$current_uid = fread($uid_file, $uid_f_size);
				if ($current_uid === FALSE) {
					goto cleanup_and_throw_file_error;
				}
			} else {
				$current_uid = '0x0';
			}

			if (fseek($uid_file, 0) == -1) {
				goto cleanup_and_throw_file_error;
			}
			$new_uid = _uid_next($current_uid);
			$ret = fwrite($uid_file, $new_uid);
			flock($uid_file, LOCK_UN);
			fclose($uid_file);

			if ($ret === FALSE) {
				return FALSE;
			} else {
				return $new_uid;
			}
		} else {
			fclose($uid_file);
			return FALSE;
		}

		// Error handling.
		cleanup_and_throw_file_error:
			flock($uid_file, LOCK_UN);
			fclose($uid_file);
			throw new Exception('UID file access failed!');
	}
