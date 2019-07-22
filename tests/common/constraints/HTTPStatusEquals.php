<?php

namespace libresignage\tests\common\constraints;

use \PHPUnit\Framework\Constraint\Constraint;
use \GuzzleHttp\Psr7\Response;

class HTTPStatusEquals extends Constraint {
	private $expect = NULL;

	public function __construct(int $expect) {
		$this->expect = $expect;
	}

	public function matches($other): bool {
		assert($other instanceof Response);

		return $this->expect === $other->getStatusCode();
	}

	protected function failureDescription($other): string {
		assert($other instanceof Response);

		return "{$other->getStatusCode()} matches the expected ".
			"HTTP status {$this->expect}. Response dump:\n".
			(string) $other->getBody();
	}

	public function toString(): string {}
}
