<?php
	/*
	*  UID generator functions for LibreSignage.
	*/

	require_once(LIBRESIGNAGE_ROOT.'/common/php/util.php');

	function get_uid(int $length = DEFAULT_UID_LEN): string {
		return bin2hex(random_bytes(ceil($length/2)));
	}
