<?php

namespace libresignage\tests\backend\api\endpoint\queue;

use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\APIInterface;
use libresignage\tests\backend\common\classes\QueueUtils;
use libresignage\api\HTTPStatus;

class queue_remove extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	const TEST_QUEUE_NAME = 'test_queue';

	private $queue_removed = FALSE;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('queue/queue_remove.php');

		// Create an initial queue to remove.
		$this->api->login('admin', 'admin');

		APIInterface::assert_success(QueueUtils::create(
			$this->api,
			self::TEST_QUEUE_NAME
		), 'Failed to create initial queue.', [$this, 'abort']);

		$this->api->logout();
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(
		string $user,
		string $pass,
		array $params,
		int $error
	): void {
		$resp = $this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			$user,
			$pass
		);
		$this->queue_removed = ($resp->getStatusCode() === HTTPStatus::OK);
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				'admin',
				'admin',
				['name' => self::TEST_QUEUE_NAME],
				HTTPStatus::OK
			],
			'Missing name parameter' => [
				'admin',
				'admin',
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Empty name parameter' => [
				'admin',
				'admin',
				['name' => ''],
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for name paremeter' => [
				'admin',
				'admin',
				['name' => TRUE],
				HTTPStatus::BAD_REQUEST
			],
			'User display tries to remove empty(!) queue of user admin' => [
				'display',
				'display',
				['name' => self::TEST_QUEUE_NAME],
				HTTPStatus::UNAUTHORIZED
			],
			'User user tries to remove empty(!) queue of user admin' => [
				'user',
				'user',
				['name' => self::TEST_QUEUE_NAME],
				HTTPStatus::OK
			]
		];
	}

	public function test_is_response_schema_correct() {
		$resp = $this->call_api_and_check_response_schema(
			['name' => self::TEST_QUEUE_NAME],
			[],
			dirname(__FILE__).'/schemas/queue_remove.schema.json',
			'admin',
			'admin'
		);
		$this->queue_removed = ($resp->getStatusCode() === HTTPStatus::OK);		
	}

	public function tearDown(): void {
		if (!$this->queue_removed) {
			$this->api->login('admin', 'admin');

			APIInterface::assert_success(QueueUtils::remove(
				$this->api,
				self::TEST_QUEUE_NAME
			), 'Failed to remove initial queue.', [$this->api, 'logout']);

			$this->api->logout();
		}
	}
}
