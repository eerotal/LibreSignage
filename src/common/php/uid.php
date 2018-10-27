<?php
	/*
	*  UID generator implementation for LibreSignage. The UIDs
	*  generated with get_uid() are guaranteed to be unique in
	*  the context of LibreSignage, since they are successive
	*  hexadecimal integers.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

	define('UID_MAX_SIZE',	PHP_INT_SIZE);
	define('UID_FILE',	LIBRESIGNAGE_ROOT.'/data/uid/uid.dat');

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
		try {
			$current_uid = file_lock_and_get(UID_FILE);
			$current_uid = preg_replace('/\s+/', '', $current_uid);
			if (empty($current_uid)) {
				$current_uid = '0x0';
			}
		} catch (Exception $e) {
			$current_uid = '0x0';
		}

		$new_uid = _uid_next($current_uid);
		file_lock_and_put(UID_FILE, $new_uid);

		return $new_uid;
	}
