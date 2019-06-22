<?php

namespace constraints;

use PHPUnit\Framework\Constraint\Constraint;
use classes\APIInterface;

class HTTPStatusEquals extends Constraint {
	private $api = NULL;
	private $expect = NULL;

	public function __construct(APIInterface $api, int $expect) {
		$this->api = $api;
		$this->expect = $expect;
	}

	public function matches($other): bool {
		return $this->expect === $other;
	}

	protected function failureDescription($other): string {
		return "{$other} {$this->toString()}";
	}

	public function toString(): string {
		return "matches the expected HTTP status {$this->expect}";
	}
}
