<?php

namespace libresignage\tests\api\endpoint\queue;

use libresignage\tests\common\classes\APITestCase;
use libresignage\tests\common\classes\APIInterface;
use libresignage\tests\common\classes\QueueUtils;
use libresignage\api\HTTPStatus;

class queue_create extends APITestCase {
	use \libresignage\tests\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	const TEST_QUEUE_NAME = 'test_queue';

	private $queue_created = FALSE;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('queue/queue_create.php');
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(array $params, int $error): void {
		$resp = $this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			'admin',
			'admin'
		);
		if ($resp->getStatusCode() === HTTPStatus::OK) {
			$this->queue_created = TRUE;
		}
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				['name' => self::TEST_QUEUE_NAME],
				HTTPStatus::OK
			],
			'Missing name parameter' => [
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Empty name parameter' => [
				['name' => ''],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for name paremeter' => [
				['name' => TRUE],
				HTTPStatus::BAD_REQUEST
			]
		];
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['name' => self::TEST_QUEUE_NAME],
			[]
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/queue_create.schema.json'
		);

		$this->api->logout();
	}

	public function tearDown(): void {
		if ($this->queue_created) {
			$this->api->login('admin', 'admin');

			APIInterface::assert_success(QueueUtils::remove(
				$this->api,
				self::TEST_QUEUE_NAME
			), 'Failed to remove initial queue.', [$this->api, 'logout']);

			$this->queue_created = FALSE;
			$this->api->logout();
		}
	}
}
