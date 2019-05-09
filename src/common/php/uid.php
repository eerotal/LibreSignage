<?php
	/*
	*  UID generator functions for LibreSignage.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

	function get_uid(int $length = DEFAULT_UID_LEN): string {
		return bin2hex(random_bytes(ceil($length/2)));
	}
