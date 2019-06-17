<?php

namespace constraints;

use PHPUnit\Framework\Constraint\Constraint;
use classes\APIInterface;

class APIErrorEquals extends Constraint {
	private $api = NULL;
	private $expect = NULL;

	public function __construct(APIInterface $api, string $expect) {
		$this->api = $api;
		$this->expect = $expect;
	}

	public function matches($other): bool {
		return $this->api->get_error_code($this->expect) === $other;
	}

	protected function failureDescription($other): string {
		return $this->api->get_error_name($other) . ' ' . $this->toString();
	}

	public function toString(): string {
		return 'matches the expected API error '.$this->expect;
	}
}
