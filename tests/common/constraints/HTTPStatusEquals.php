<?php

namespace constraints;

use \PHPUnit\Framework\Constraint\Constraint;
use \GuzzleHttp\Psr7\Response;

class HTTPStatusEquals extends Constraint {
	private $expect = NULL;

	public function __construct(int $expect) {
		$this->expect = $expect;
	}

	public function matches(Response $other): bool {
		return $this->expect === $other->getStatusCode();
	}

	protected function failureDescription(Response $other): string {
		return "{$other->getStatusCode()} matches the expected ".
			"HTTP status {$this->expect}. Response dump:\n".
			(string) $other->getBody();
	}

	public function toString(): string {}
}
