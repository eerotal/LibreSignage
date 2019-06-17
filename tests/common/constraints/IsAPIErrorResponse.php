<?php

namespace constraints;

use PHPUnit\Framework\Constraint\Constraint;
use JsonSchema\Validator;
use classes\APIInterface;
use classes\APITestUtils;

class IsAPIErrorResponse extends Constraint {
	private $api = NULL;
	private $expect = NULL;

	public function __construct(APIInterface $api, string $expect) {
		$this->api = $api;
		$this->expect = $expect;
	}

	public function matches($response): bool {
		$schema = APITestUtils::read_json_file(SCHEMA_PATH.'/error.schema.json');
		$validator = new Validator();
		$validator->validate($response, $schema);
		return $validator->isValid();
	}

	protected function failureDescription($other): string {
		return $this->toString();
	}

	public function toString(): string {
		return 'API response matches supplied JSON schema.';
	}
}
