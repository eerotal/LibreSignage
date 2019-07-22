<?php

namespace libresignage\tests\common\constraints;

use \PHPUnit\Framework\Constraint\Constraint;
use \GuzzleHttp\Psr7\Response;

class HeaderExists extends Constraint {
	private $header = NULL;

	/**
	* Construct the header constraint.
	*
	* @parma string $header The expected header name.
	*/
	public function __construct(string $header) {
		$this->header = $header;
	}

	public function matches($other): bool {
		assert($other instanceof Response);

		return $other->hasHeader($this->header);
	}

	protected function failureDescription($other): string {
		assert($other instanceof Response);

		return "response has expected header {$this->header}";
	}

	public function toString(): string {}
}
