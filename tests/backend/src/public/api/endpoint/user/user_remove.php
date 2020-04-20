<?php

namespace libresignage\tests\backend\api\endpoint\user;

use \JsonSchema\Validator;
use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\APITestUtils;
use libresignage\tests\backend\common\classes\APIInterface;
use libresignage\tests\backend\common\classes\UserUtils;
use libresignage\api\HTTPStatus;

class user_remove extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	const UNIT_TEST_USER = 'unit_test_user';

	private $user_removed = FALSE;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('user/user_remove.php');

		// Create an initial user that the tests try to remove.
		$this->api->login('admin', 'admin');

		APIInterface::assert_success(UserUtils::create(
			$this->api,
			self::UNIT_TEST_USER,
			['editor', 'display']
		), 'Failed to create initial user.', [$this, 'abort']);

		$this->api->logout();
	}

	public function test_endpoint_not_authorized_for_non_admin_users(): void {
		$this->api->login('user', 'user');

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['user' => self::UNIT_TEST_USER],
			[],
			TRUE
		);
		$this->user_removed = ($resp->getStatusCode() === HTTPStatus::OK);

		$this->assert_api_failed($resp, HTTPStatus::UNAUTHORIZED);

		$this->api->logout();
	}


	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['user' => self::UNIT_TEST_USER],
			[],
			TRUE
		);
		$this->user_removed = ($resp->getStatusCode() === HTTPStatus::OK);

		$this->assert_object_matches_schema(
			APIInterface::decode_raw_response($resp),
			dirname(__FILE__).'/schemas/user_remove.schema.json'
		);

		$this->api->logout();
	}

	public function tearDown(): void {
		// Remove the initial user in case it wasn't successfully removed.
		if (!$this->user_removed) {
			$this->api->login('admin', 'admin');

			APIInterface::assert_success(UserUtils::remove(
				$this->api,
				self::UNIT_TEST_USER
			), 'Failed to remove initial user.', [$this->api, 'logout']);
			$this->user_removed = FALSE;

			$this->api->logout();
		}
	}
}
