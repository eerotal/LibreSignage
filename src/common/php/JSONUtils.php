<?php

namespace libresignage\common\php;

use libresignage\common\php\exceptions\JSONException;

/**
* Utility functions for encoding and decoding JSON. These
* functions wrap json_encode() and json_decode() to provide
* a more object oriented interface and to improve error handling.
*/
final class JSONUtils {
	/**
	* Function for JSON encoding an object. This function wraps
	* the builtin function json_decode() but handles errors by
	* throwing an exception if the encoding fails.
	*
	* @param mixed $args All arguments are passed to the builtin
	*                    function json_encode().
	* @throws JSONException if encoding fails.
	* @return string The encoded JSON string.
	*/
	public static function encode(...$args): string {
		$ret = \call_user_func_array('json_encode', $args);
		if ($ret === FALSE && \json_last_error() !== JSON_ERROR_NONE) {
			throw new JSONException(
				'Failed to encode JSON: '.JSONUtils::json_error_str()
			);
		}
		return $ret;
	}

	/**
	* Function for decoding a JSON string. This function wraps
	* the builtin function json_decode() but handles errors by
	* throwing an exception if the decoding fails.
	*
	* @param mixed $args All arguments are passed to the builtin
	*                    function json_decode().
	* @throws JSONException if decoding fails.
	* @return mixed The decoded data.
	*/
	public static function decode(...$args) {
		$ret = \call_user_func_array('json_decode', $args);
		if ($ret === NULL && \json_last_error() !== JSON_ERROR_NONE) {
			throw new JSONException(
				'Failed to decode JSON: '.JSONUtils::json_error_str()
			);
		}
		return $ret;
	}

	/**
	* Return a string describing the error that occured the last
	* time JSONUtils::encode() or JSONUtils::decode() was called.
	* This function also returns errors for json_encode() and
	* json_decode() since JSONUtils uses them under the hood.
	*
	* @return string A string describing the error.
	*/
	public static function json_error_str(): string {
		switch(\json_last_error()) {
			case JSON_ERROR_NONE:
				return 'No error occured.';
			case JSON_ERROR_DEPTH:
				return 'Maximum stack depth exceeded.';
			case JSON_ERROR_STATE_MISMATCH:
				return 'Invalid or malformed JSON.';
			case JSON_ERROR_CTRL_CHAR:
				return 'Control character error. (Invalid encoding?)';
			case JSON_ERROR_SYNTAX:
				return 'JSON syntax error.';
			case JSON_ERROR_UTF8:
				return 'Malformed UTF-8 characters. (Invalid encoding?)';
			case JSON_ERROR_RECURSION:
				return 'Recursive reference(s) in the value to be encoded.';
			case JSON_ERROR_INF_OR_NAN:
				return 'INF or NAN value(s) in the value to be encoded.';
			case JSON_ERROR_UNSUPPORTED_TYPE:
				return 'A value of a type that cannot be encoded was given.';
			case JSON_ERROR_INVALID_PROPERTY_NAME:
				return 'A property name that cannot be encoded was given.';
			case JSON_ERROR_UTF16:
				return 'Malformed UTF-16 characters. (Invalid encoding?)';
			default:
				return 'Unknown error.';
		}
	}
}
