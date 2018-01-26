<?php
	/*
	*  LibreSignage utility functions.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

	function array_is_equal(array $a, array $b) {
		/*
		*  Check if array $a has the same values
		*  as array $b. Returns TRUE if $a is equal
		*  to $b and FALSE otherwise.
		*/
		if (array_is_subset($a, $b) &&
			count($a) == count($b)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function array_is_subset(array $a, array $b) {
		/*
		*  Check if array $a is a subset of array
		*  $b. Returns true if $a is a subset of
		*  $b and false otherwise.
		*/
		if (count(array_intersect($a, $b)) == count($a)) {
			return true;
		} else {
			return false;
		}
	}

	function error_redir(int $code) {
		$tmp = $code;
		$errors = array(
			404 => '404 Not Found',
			403 => '403 Forbidden',
			500 => '500 Internal Server Error'
		);
		if (!array_key_exists($tmp, $errors)) {
			$tmp = 500;
		}
		header($_SERVER['SERVER_PROTOCOL'].$errors[$tmp]);
		header('Location: '.ERRORS.'/'.$code);
		exit(0);
	}
