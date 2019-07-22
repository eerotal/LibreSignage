<?php

namespace libresignage\tests\common\constraints;

use \PHPUnit\Framework\Constraint\Constraint;
use \JsonSchema\Validator;
use libresignage\tests\common\classes\APITestUtils;

class IsAPIErrorResponse extends Constraint {
	private $expect = NULL;

	public function __construct(string $expect) {
		$this->expect = $expect;
	}

	public function matches($other): bool {
		assert(is_object($other));

		$schema = APITestUtils::read_json_file(SCHEMA_PATH.'/APIException.schema.json');
		$validator = new Validator();
		$validator->validate($other, $schema);
		return $validator->isValid();
	}

	protected function failureDescription($other): string {
		return $this->toString();
	}

	public function toString(): string {
		return 'API response matches error JSON schema.';
	}
}
