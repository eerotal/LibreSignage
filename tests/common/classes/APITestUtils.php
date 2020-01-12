<?php

namespace libresignage\tests\common\classes;

use \JsonSchema\Validator;
use \JsonSchema\SchemaStorage;
use \JsonSchema\Constraints\Factory;

use libresignage\common\php\JSONUtils;

final class APITestUtils {
	public static function read_json_file(string $path) {
		/*
		*  Wrapper for reading and decoding a JSON file in on go.
		*/
		return JSONUtils::decode(\file_get_contents($path));
	}

	public static function json_schema_error_string(Validator $validator): string {
		/*
		*  Build an error string from a JsonSchema\Validator object's data.
		*/
		if ($validator->isValid()) { return 'Schema validation OK.'; }

		$ret = "Schema validation failed:\n\n";
		foreach($validator->getErrors() as $e) {
			$ret .= sprintf("%s: %s\n", $e['property'], $e['message']);
		}
		return $ret;
	}

	/**
	* Return a variable pretty printed in a YAML-esque format.
	*
	* @param string|object|array $data     The data to return.
	* @param int                 $indent   The initial indent level.
	* @param array               $previous Internal variable for handling
	*                                      recursive objects.
	*/
	public static function pretty_print(
		$data,
		int $indent = 0,
		array $previous = []
	): string {
		$ret = '';
		$i = 0;

		if (is_array($data) || is_object($data)) {
			// Array or object.
			$previous[] = $data;

			foreach ($data as $key => $value) {
				$ret .= str_repeat("\t", $indent).
						(($indent !== 0 || $i++ !== 0) ? "\n" : '').
						"$key: ";

				if (in_array($value, $previous, TRUE)) {
					$ret .= 'RECURSION';
				} else {
					$ret .= self::pretty_print(
						$value,
						$indent + 1,
						$previous
					);
				}
			}
			return $ret;
		} else if (is_string($data) && substr_count($data, "\n") > 1) {
			// Multiline string.
			return 
				((1) ? "\n" : '').
				str_repeat("\t", $indent).
				preg_replace(
					'/\n/',
					"\n".str_repeat("\t", $indent),
					$data
				);
		} else {
			// Other types.
			return (string) $data;
		}
	}

}
