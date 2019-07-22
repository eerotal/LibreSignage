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
}
