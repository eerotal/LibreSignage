<?php

namespace constraints;

use \PHPUnit\Framework\Constraint\Constraint;
use \GuzzleHttp\Psr7\Response;
use \common\php\JSONUtils;

class HeaderEquals extends Constraint {
	private $header = NULL;
	private $expect = NULL;
	private $matcher = NULL;

	/**
	* Construct the header constraint.
	*
	* @param string   $header  The header name.
	* @param array    $expect  The expected header values.
	* @param callable $matcher The matcher function used for comparing the
	*                          received header values to the expected ones.
	*                          This should be a function that accepts two
	*                          array arguments: The received header values
	*                          as the first one and the expected values as
	*                          the second one.
	*/
	public function __construct(
		string $header,
		array $expect,
		callable $matcher = NULL
	) {
		$this->header = $header;
		$this->expect = $expect;

		$this->matcher = ($matcher === NULL)
			? function(array $a, array $b) { return $a == $b; }
			: $matcher;
	}

	public function matches(Response $other): bool {
		return ($this->matcher)(
			$this->expect,
			$other->getHeader($this->header)
		);
	}

	protected function failureDescription(Response $other): string {
		$got = JSONUtils::encode($other->getHeader($this->header));
		$ex = JSONUtils::encode($this->expect);

		return "the header {$this->header}: {$got} matches the ".
			"expected header {$this->header}: {$ex}";
	}

	public function toString(): string {}
}
