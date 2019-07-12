<?php

namespace common\php;

use common\php\exceptions\IntException;
use common\php\exceptions\ArgException;

/**
* LibreSignage utility functions.
*/
final class Util {
	/**
	* Recursively remove a directory and files within it.
	*
	* @param string $path The path to remove.
	*
	* @throws IntException if scandir() fails.
	* @throws IntException if unlink() fails.
	* @throws IntException if rmdir() fails.
	*/
	static function rmdir_recursive(string $path) {
		$files = scandir($path);
		if ($files === FALSE) {
			throw new IntException('scandir() failed.');
		}
		$files = array_diff($files, array('.', '..'));

		foreach ($files as $f) {
			if (is_dir($path.'/'.$f)) {
				self::rmdir_recursive($path.'/'.$f);
			} else {
				if (!unlink($path.'/'.$f)) {
					throw new IntException('unlink() failed.');
				}
			}
		}
		if (!rmdir($path)) {
			throw new IntException('rmdir() failed.');
		}
	}

	/**
	* Check if array $a has the same values as array $b. Returns
	* TRUE if $a is equal to $b and FALSE otherwise.
	*
	* @params array $a Set A.
	* @params array $b Set B.
	* @returns bool TRUE if the values of $a and $b match, FALSE otherwise.
	*/
	static function array_is_equal(array $a, array $b): bool {
		return array_is_subset($a, $b) && count($a) == count($b);
	}

	/*
	* Check if array $a is a subset of array $b. Returns TRUE if $a is a
	* subset of $b and FALSE otherwise.
	*
	* @param array $a Set A.
	* @param array $b Set B.
	* @return bool TRUE if $a E $b and FALSE otherwise.
	*/
	static function array_is_subset(array $a, array $b): bool {
		return count(array_intersect($a, $b)) == count($a);
	}

	/*
	* Wrapper for file_get_contents() that acquires a shared
	* lock before reading any data.
	*
	* @params string $path The path to the file to read.
	* @return string The data read from file.
	* @throws IntException if fopen() fails.
	* @throws IntException if locking the file fails.
	*/
	static function file_lock_and_get(string $path): string {
		$ret = '';

		clearstatcache();
		$fp = fopen($path, 'r');

		if ($fp === FALSE) {
			throw new IntException('Failed to open file for reading.');
		}
		if (flock($fp, LOCK_SH)) {
			$fs = filesize($path);
			if ($fs === 0) { return ''; }

			$ret = fread($fp, $fs);
			flock($fp, LOCK_UN);
		} else {
			fclose($fp);
			throw new IntException('Failed to lock file.');
		}
		fclose($fp);
		return $ret;
	}

	/*
	* Wrapper for file_put_contents() with the LOCK_EX flag set.
	* This function also recursively creates the path to the file
	* if $create == TRUE and the path doesn't already exist.
	*
	* @param string $path The path to the file to write.
	* @param string $data The data to write to the file.
	* @param bool $create Whether to create paths that don't exist.
	* @throws IntException if creating the file dir fails ($create === TRUE).
	* @throws ArgException if the file dir doesn't exist ($create === FALSE).
	* @throws ArgException if the file doesn't exist ($create === FALSE).
	* @throws IntException if writing the file fails.
	*/
	static function file_lock_and_put(
		string $path,
		string $data,
		bool $create = TRUE
	) {
		if (!is_dir(dirname($path))) {
			if ($create) {
				if (!mkdir(dirname($path), 0775, TRUE)) {
					throw new IntException(
						'Failed to create directory.'
					);
				}
			} else {
				throw new ArgException("Directory doesn't exist.");
			}
		}
		if (!is_file($path) && !$create) {
			throw new ArgException("File doesn't exist.");
		}

		$ret = file_put_contents($path, $data, LOCK_EX);
		clearstatcache();

		if ($ret === FALSE) {
			throw new IntException('Failed to write file.');
		}
	}

	/**
	* Generate a random password with $len chars in it.
	*
	* @param int $len The length of the generated password.
	* @return string The generated password.
	*/
	static function gen_passwd(int $len): string {
		$chr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
				'abcdefghijklmnopqrstuvwxyz'.
				'0123456789-_';
		$ret = '';
		for ($i = 0; $i < $len; $i++) {
			$ret .= substr($chr, random_int(0, strlen($chr) - 1), 1);
		}
		return $ret;
	}

	/**
	* Call $func with each element of $arr as an argument and
	* return TRUE if every call to $func returns TRUE. Otherwise
	* return false.
	*
	* @param array $arr The array to check.
	* @param Callable $func The function used for checking the array.
	* @return bool The result of the operation.
	*/
	static function array_check(array $arr, Callable $func): bool {
		foreach ($arr as $a) {
			if (!$func($a)) { return FALSE; }
		}
		return TRUE;
	}

	/**
	* Create a HTML tag.
	*
	* @param string $type The HTML tag type, for example div, p or table etc.
	* @param string $content The inner HTML of the tag.
	* @param array @params An associative array of parameters for the tag.
	* @return string The generated HTML tag string.
	*/
	static function htmltag(string $type, string $content, array $params): string {
		$ret = "<$type";
		foreach ($params as $k => $v) { $ret .= ' '.$k.'="'.$v.'"'; }
		$ret .= ">$content</$type>";
		return $ret;
	}

	/**
	* Check the differences between $arr1 and $arr2. This function
	* returns an array with two keys: missing and extra. The missing
	* key contains all keys in $arr1 missing from $arr2. The extra
	* key contains all keys in $arr2 missing from $arr1.
	*
	* @param array $arr1 First array for comparison.
	* @param array $arr2 Second array for comparison.
	* @return array The return value described in the function description.
	*/
	static function arraydiff(array $arr1, array $arr2): array {
		$missing = [];
		$extra = [];

		foreach ($arr1 as $v) {
			if (!in_array($v, $arr2)) {
				$missing[] = $v;
			}
		}
		foreach ($arr2 as $v) {
			if (!in_array($v, $arr1)) {
				$extra[] = $v;
			}
		}
		return [
			'missing' => $missing,
			'extra' => $extra
		];
	}

	/**
	* Generate a new UID.
	*
	* @param int $length The length of the new UID in characters.
	* @return string The generated UID.
	*/
	public static function get_uid(int $len = NULL): string {
		$len = ($len === NULL) ? Config::config('DEFAULT_UID_LEN') : $len;
		return bin2hex(random_bytes(ceil($len/2)));
	}
}
