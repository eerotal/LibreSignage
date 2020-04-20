<?php

namespace libresignage\tests\backend\common\classes;

/**
* Class for storing API session data.
*/
class APISession {
	private $username = '';
	private $token = '';
	private $backtrace = '';

	public function __construct(string $username, string $token) {
		$this->username = $username;
		$this->token = $token;
		$this->backtrace = (new \Exception())->getTraceAsString();
	}

	public function get_username(): string { return $this->username; }
	public function get_token(): string { return $this->token; }
	public function get_backtrace(): string { return $this->backtrace; }
}
