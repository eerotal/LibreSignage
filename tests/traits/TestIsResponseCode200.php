<?php

namespace traits;

trait TestIsResponseCode200 {
	public function test_is_response_code_200(): void {
		$response = $this->client->get($this->get_endpoint_uri());
		$this->assertEquals(200, $response->getStatusCode());
	}
}
