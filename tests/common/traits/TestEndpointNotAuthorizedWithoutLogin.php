<?php

namespace traits;

trait TestEndpointNotAuthorizedWithoutLogin {
	public function test_endpoint_not_authorized_without_login(): void {
		// Make sure no session is active.
		$this->api->logout();

		$response = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[],
			[],
			TRUE
		);
		$this->assert_api_failed($response, 401);
	}
}
