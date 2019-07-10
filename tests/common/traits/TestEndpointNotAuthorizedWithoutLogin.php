<?php

namespace traits;

use \api\HTTPStatus;

trait TestEndpointNotAuthorizedWithoutLogin {
	public function test_endpoint_not_authorized_without_login(): void {
		// Make sure no session is active.
		$this->api->logout();

		$this->call_api_and_assert_failed(
			[],
			[],
			HTTPStatus::UNAUTHORIZED,
			NULL,
			NULL
		);
	}
}
