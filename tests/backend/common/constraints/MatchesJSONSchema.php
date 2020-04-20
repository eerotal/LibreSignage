<?php

namespace libresignage\tests\backend\common\constraints;

use \PHPUnit\Framework\Constraint\Constraint;
use \JsonSchema\Validator;
use libresignage\tests\backend\common\classes\APITestUtils;

use libresignage\common\php\JSONUtils;
use libresignage\common\php\JSONException;

class MatchesJSONSchema extends Constraint {
	private $schema = NULL;
	private $validator = NULL;

	public function __construct(string $schema_path) {
		$this->validator = new Validator();
		$this->schema = APITestUtils::read_json_file($schema_path);
	}

	public function matches($other): bool {
		assert(is_object($other));

		$this->validator->validate($other, $this->schema);
		return $this->validator->isValid();
	}

	protected function failureDescription($other): string {
		assert(is_object($other));

		$pretty = JSONUtils::encode($other, JSON_PRETTY_PRINT);
		return "the following JSON matches the provided schema:\n".
				$pretty."\n".
				$this->toString();
	}

	public function toString(): string {
		return APITestUtils::json_schema_error_string($this->validator);
	}
}
