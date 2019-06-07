<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

abstract class APITestCase extends TestCase {
	public $client    = NULL;
	public $validator = NULL;

	public function setUp(): void {
		$host = getenv('PHPUNIT_API_HOST', TRUE);
		assert(!empty($host), "'PHPUNIT_API_HOST' env variable not set.");

		$this->client = new Client(['base_uri' => "{$host}/api/endpoint/"]);
	}
}
