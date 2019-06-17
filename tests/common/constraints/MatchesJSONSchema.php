<?php

namespace constraints;

use PHPUnit\Framework\Constraint\Constraint;
use JsonSchema\Validator;
use classes\APITestUtils;

class MatchesJSONSchema extends Constraint {
	private $schema = NULL;
	private $validator = NULL;

	public function __construct(string $schema_path) {
		$this->validator = new Validator();
		$this->schema = APITestUtils::read_json_file($schema_path);
	}

	public function matches($other): bool {
		$this->validator->validate($other, $this->schema);
		return $this->validator->isValid();
	}

	protected function failureDescription($other): string {
		$pretty = APITestUtils::json_encode($other, JSON_PRETTY_PRINT);
		return "the following JSON matches the provided schema:\n".
				$pretty."\n".
				$this->toString();
	}

	public function toString(): string {
		return APITestUtils::json_schema_error_string($this->validator);
	}
}
