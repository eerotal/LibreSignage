<?php

namespace constraints;

use PHPUnit\Framework\Constraint\Constraint;
use GuzzleHttp\Psr7\Response;
use classes\APIInterface;

class HTTPStatusEquals extends Constraint {
	private $api = NULL;
	private $expect = NULL;

	public function __construct(APIInterface $api, int $expect) {
		$this->api = $api;
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
