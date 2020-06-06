<?php

namespace libresignage\tests\backend\src\pub\api\endpoint\queue;

use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\APIInterface;
use libresignage\tests\backend\common\classes\QueueUtils;
use libresignage\tests\backend\common\classes\SlideUtils;
use libresignage\common\php\Config;
use libresignage\api\HTTPStatus;

class queue_remove_slide extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	const TEST_QUEUE_NAME_1 = 'test_queue_1';
	const TEST_QUEUE_NAME_2 = 'test_queue_2';

	private $slide_id = NULL;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('queue/queue_remove_slide.php');

		$this->api->login('admin', 'admin');

		// Create two queues for testing.
		APIInterface::assert_success(
			QueueUtils::create(
				$this->api,
				self::TEST_QUEUE_NAME_1
			),
			'Failed to create initial queue.',
			[$this, 'abort']
		);
		APIInterface::assert_success(
			QueueUtils::create(
				$this->api,
				self::TEST_QUEUE_NAME_2
			),
			'Failed to create initial queue.',
			[$this, 'abort']
		);

		// Create a slide for testing.
		$ret = SlideUtils::save(
			$this->api,
			NULL,
			'test-slide',
			Config::limit('SLIDE_MIN_DURATION'),
			'',
			TRUE,
			FALSE,
			0,
			0,
			0,
			[]
		);
		APIInterface::assert_success($ret);
		$this->slide_id = APIInterface::decode_raw_response($ret)->slide->id;

		/*
		 * Add the slide to both queues because this endpoint can't
		 * be used if the slide is to be removed from all queues the
		 * slide is in.
		 */
		APIInterface::assert_success(
			QueueUtils::add_slide(
				$this->api,
				self::TEST_QUEUE_NAME_1,
				$this->slide_id,
				0
			)
		);
		APIInterface::assert_success(
			QueueUtils::add_slide(
				$this->api,
				self::TEST_QUEUE_NAME_2,
				$this->slide_id,
				0
			)
		);
		$this->api->logout();
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(
		array $params,
		bool $no_slide_id,
		int $error
	): void {
		/*
		* Use the stored slide ID if no ID is provided by
		* params_provider() and $no_slide_id is FALSE.
		*/
		if (!array_key_exists('slide_id', $params) && !$no_slide_id) {
			$params['slide_id'] = $this->slide_id;
		}

		$resp = $this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			'admin',
			'admin'
		);
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				['queue_name' => self::TEST_QUEUE_NAME_1],
				FALSE,
				HTTPStatus::OK
			],
			'Nonexistent queue' => [
				['queue_name' => 'aabbcc',],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for queue_name parameter' => [
				['queue_name' => 10,],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Empty queue_name parameter' => [
				['queue_name' => ''],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Missing queue_name parameter' => [
				[],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Nonexistent slide' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME_1,
					'slide_id' => 'aabbcc'
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for slide_id parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME_1,
					'slide_id' => 10
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Empty slide_id parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME_1,
					'slide_id' => ''
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Missing slide_id parameter' => [
				['queue_name' => self::TEST_QUEUE_NAME_1],
				TRUE,
				HTTPStatus::BAD_REQUEST
			]
		];
	}

	public function test_slide_cant_be_removed_from_both_queues(): void {
		$resp = $this->call_api_and_assert_failed(
			[
				'queue_name' => self::TEST_QUEUE_NAME_1,
				'slide_id' => $this->slide_id
			],
			[],
			HTTPStatus::OK,
			'admin',
			'admin'
		);
		$resp = $this->call_api_and_assert_failed(
			[
				'queue_name' => self::TEST_QUEUE_NAME_2,
				'slide_id' => $this->slide_id
			],
			[],
			HTTPStatus::FORBIDDEN,
			'admin',
			'admin'
		);
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[
				'queue_name' => self::TEST_QUEUE_NAME_1,
				'slide_id' => $this->slide_id
			],
			[]
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/queue_remove_slide.schema.json'
		);

		$this->api->logout();
	}

	public function tearDown(): void {
		$this->api->login('admin', 'admin');

		// Remove the testing queues.
		APIInterface::assert_success(
			QueueUtils::remove(
				$this->api,
				self::TEST_QUEUE_NAME_1
			),
			'Failed to remove initial queue 1.',
			[$this->api, 'logout']
		);
		APIInterface::assert_success(
			QueueUtils::remove(
				$this->api,
				self::TEST_QUEUE_NAME_2
			),
			'Failed to remove initial queue 2.',
			[$this->api, 'logout']
		);

		$this->api->logout();
	}
}
