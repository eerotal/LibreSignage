<?php

namespace traits;

trait TestIsResponseContentTypeJSON {
	public function test_is_response_content_type_JSON() {
		$response = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[],
			[],
			TRUE
		);
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
