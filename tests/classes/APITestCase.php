<?php

namespace classes;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class APITestCase extends TestCase {
	public $client       = NULL;
	public $endpoint_uri = NULL;

	public function setUp(): void {
		$host = getenv('PHPUNIT_API_HOST', TRUE);
		assert(!empty($host), "'PHPUNIT_API_HOST' env variable not set.");

		$this->client = new Client(['base_uri' => "{$host}/api/endpoint/"]);
	}

	public function set_endpoint_uri(string $uri) {
		$this->endpoint_uri = $uri;
	}

	public function get_endpoint_uri(): string {
		return $this->endpoint_uri;
	}
}
