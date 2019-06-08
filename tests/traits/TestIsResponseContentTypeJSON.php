<?php

namespace traits;

trait TestIsResponseContentTypeJSON {
	public function test_is_response_content_type_JSON() {
		$response = $this->client->get($this->get_endpoint_uri());
		$this->assertEquals(
			TRUE,
			$response->hasHeader('Content-Type')
		);
		$this->assertEquals(
			'application/json',
			$response->getHeader('Content-Type')[0]
		);
	}
}
