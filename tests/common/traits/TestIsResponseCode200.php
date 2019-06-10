<?php

namespace traits;

trait TestIsResponseCode200 {
	public function test_is_response_code_200(): void {
		$response = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[],
			[],
			TRUE
		);
		$this->assertEquals(200, $response->getStatusCode());
	}
}
