<?php

namespace traits;

trait TestIsResponseContentTypeJSON {
	public function test_is_response_content_type_JSON() {
		$response = $this->client->get('general/api_err_codes.php');
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
