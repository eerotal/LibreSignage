<?php

namespace classes;

use JsonSchema\Validator;

class APITestUtils {
	public static function json_decode(string $str) {
		$ret = json_decode($str);
		if ($ret === NULL && json_last_error() !== JSON_ERROR_NONE) {
			throw new Exception('Failed to decode JSON.');
		}
		return $ret;
	}

	public static function json_schema_error_string(Validator $validator): string {
		if ($validator->isValid()) { return 'Schema validation OK.'; }

		$ret = "Schema validation failed:\n\n";
		foreach($validator->getErrors() as $e) {
			$ret .= sprintf("%s: %s\n", $e['property'], $e['message']);
		}
		return $ret;
	}
}
