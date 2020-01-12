<?php

namespace libresignage\tests\api\endpoint\user;

use libresignage\tests\common\classes\APITestCase;
use libresignage\tests\common\classes\APITestUtils;
use libresignage\tests\common\classes\APIInterface;
use libresignage\tests\common\classes\UserUtils;
use libresignage\api\HTTPStatus;

class user_create extends APITestCase {
	use \libresignage\tests\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	const UNIT_TEST_USER = 'unit_test_user';

	private $user_created = FALSE;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('user/user_create.php');
	}

	/**
	 * @dataProvider params_provider
	 */
	public function test_fuzz_params(
		array $params,
		int $error
	): void {
		$resp = $this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			'admin',
			'admin'
		);
		$this->user_created = ($resp->getStatusCode() === HTTPStatus::OK);
	}

	public function params_provider(): array {
		return [
			'Valid parameters' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => ['editor', 'display']
				],
				HTTPStatus::OK
			],
			'Wrong type for user parameter' => [
				[
					'user' => 1,
					'groups' => ['editor', 'display']
				],
				HTTPStatus::BAD_REQUEST
			],
			'Empty username' => [
				[
					'user' => '',
					'groups' => ['editor', 'display']
				],
				HTTPStatus::BAD_REQUEST
			],
			'NULL username' => [
				[
					'user' => NULL,
					'groups' => ['editor', 'display']
				],
				HTTPStatus::BAD_REQUEST
			],
			'No groups parameter' => [
				[
					'user' => self::UNIT_TEST_USER
				],
				HTTPStatus::OK
			],
			'Wrong type for groups parameter' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => 'wrong_type'
				],
				HTTPStatus::BAD_REQUEST
			],
			'Empty groups array' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => []
				],
				HTTPStatus::OK
			],
			'NULL groups parameter' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => NULL
				],
				HTTPStatus::OK
			],
			'Empty group name in groups array' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => ['']
				],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type in groups array' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => [1, 2, 3]
				],
				HTTPStatus::BAD_REQUEST
			],
			'No parameters' => [
				[],
				HTTPStatus::BAD_REQUEST
			]
		];
	}

	public function test_invalid_request_error_on_existing_user(): void {
		$this->api->login('admin', 'admin');

		$resp = [];
		for ($i = 0; $i < 2; $i++) {
			$resp[$i] = $this->api->call_return_raw_response(
				$this->get_endpoint_method(),
				$this->get_endpoint_uri(),
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => ['editor', 'display']
				],
				[],
				TRUE
			);
		}

		$this->assert_api_succeeded($resp[0]);
		$this->user_created = TRUE;

		$this->assert_api_failed($resp[1], 400);

		$this->api->logout();
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[
				'user' => self::UNIT_TEST_USER,
				'groups' => ['editor', 'display']
			],
			[],
			TRUE
		);
		$this->user_created = ($resp->getStatusCode() === HTTPStatus::OK);

		$this->assert_object_matches_schema(
			APIInterface::decode_raw_response($resp),
			dirname(__FILE__).'/schemas/user_create.schema.json'
		);

		$this->api->logout();
	}

	public function test_endpoint_not_authorized_for_non_admin_users(): void {
		$this->api->login('user', 'user');

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[
				'user' => self::UNIT_TEST_USER,
				'groups' => ['editor', 'display']
			],
			[],
			TRUE
		);
		$this->user_created = ($resp->getStatusCode() === HTTPStatus::OK);
		$this->assert_api_failed($resp, 401);

		$this->api->logout();
	}

	public function tearDown(): void {
		if ($this->user_created) {
			$this->api->login('admin', 'admin');

			APIInterface::assert_success(UserUtils::remove(
				$this->api,
				self::UNIT_TEST_USER
			), 'Failed to cleanup created user.', [$this->api, 'logout']);
			$this->user_created = FALSE;

			$this->api->logout();
		}
	}
}
