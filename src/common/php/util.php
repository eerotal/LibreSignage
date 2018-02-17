<?php
/*
*  LibreSignage utility functions.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

function rmdir_recursive($path) {
	/*
	*  Recursively remove a directory and
	*  files within it.
	*/
	$files = @scandir($path);
	if ($files === FALSE) {
		throw new IntException('scandir() failed.');
	}

	$files = array_diff($files, array('.', '..'));

	foreach ($files as $f) {
		if (@is_dir($path.'/'.$f)) {
			rmdir_recursive($path.'/'.$f);
		} else {
			if (!@unlink($path.'/'.$f)) {
				throw new IntException('unlink() failed.');
			}
		}
	}
	if (!@rmdir($path)) {
		throw new IntException('rmdir() failed.');
	}
}

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

function file_lock_and_get(string $path) {
	/*
	*  Basically the same thing as file_get_contents()
	*  but this function acquires an exclusive lock
	*  before reading any data.
	*/
	$ret = '';
	$fs = filesize($path);
	$fp = @fopen($path, 'r');

	if ($fp === FALSE) {
		throw new IntException('Failed to open file for reading.');
	}
	if (flock($fp, LOCK_EX)) {
		if ($fs == 0) { return ''; }
		$ret = @fread($fp, $fs);
		flock($fp, LOCK_UN);
	} else {
		throw new IntException('Failed to lock file.');
	}
	fclose($fp);
	return $ret;
}

function file_lock_and_put(string $path,
			string $data,
			bool $create = TRUE) {
	/*
	*  Wrapper for file_put_contents() with the
	*  LOCK_EX flag set. This function also
	*  recursively creates the path to the file
	*  if $create == TRUE and the path doesn't
	*  already exist.
	*/
	if (!is_dir(dirname($path))) {
		if ($create) {
			if (@!mkdir(dirname($path), 0775, TRUE)) {
				throw new IntException('Failed to create '.
						'directory.');
			}
		} else {
			throw new Exception("Directory doesn't exist.");
		}
	}
	if (!is_file($path) && !$create) {
		throw new Exception("File doesn't exist.");
	}

	$ret = file_put_contents($path, $data, LOCK_EX);
	if ($ret === FALSE) {
		throw new IntException('Failed to write file.');
	}
}

function gen_passwd(int $len) {
	/*
	*  Generate a random password with $len chars in it.
	*/
	$chr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
		'abcdefghijklmnopqrstuvwxyz'.
		'0123456789-_';
	$ret = '';
	for ($i = 0; $i < $len; $i++) {
		$ret .= substr($chr, random_int(0,
				strlen($chr) - 1), 1);
	}
	return $ret;
}
