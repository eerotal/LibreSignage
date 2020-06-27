<?php

namespace libresignage\tests\backend\src\pub\api\endpoint\queue;

use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\APIInterface;
use libresignage\tests\backend\common\classes\QueueUtils;
use libresignage\tests\backend\common\classes\SlideUtils;
use libresignage\common\php\Config;
use libresignage\api\HTTPStatus;
use libresignage\common\php\queue\Queue;

class queue_reorder extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	const TEST_QUEUE_NAME = 'test_queue';

	private $slide_id = NULL;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('queue/queue_reorder.php');

		$this->api->login('admin', 'admin');

		// Create a queue for testing.
		APIInterface::assert_success(
			QueueUtils::create(
				$this->api,
				self::TEST_QUEUE_NAME
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

		try {
			// Add the slide to the queue.
			APIInterface::assert_success(
				QueueUtils::add_slide(
					$this->api,
					self::TEST_QUEUE_NAME,
					$this->slide_id,
					0
				)
			);
		} catch (\Exception $e) {
			/*
			 * Remove the created slide if adding it to the queue fails.
			 * The rest of the cleanup if done by $this->tearDown().
			 */
			SlideUtils::remove($this->api, $this->slide_id);
			throw $e;
		}

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

		$this->api->login('admin', 'admin');
		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$params,
			[],
			TRUE
		);
		try {
			$this->assert_api_failed($resp, $error);
		} catch (\Exception $e) {
			$this->api->logout();
			throw $e;
		}

		/**
		 * If the call returned HTTP OK, check that the
		 * Queue was properly reordered.
		 */
		if ($resp->getStatusCode() === HTTPStatus::OK) {
			try {
				assert(
					array_key_exists('queue_name', $params),
					"'queue_name' not in params but endpoint returned OK! ".
					"This shouldn't happen, fix your tests."
				);
				assert(
					array_key_exists('slide_id', $params),
					"'slide_id' not in params but endpoint returned OK! ".
					"This shouldn't happen, fix your tests."
				);
				assert(
					array_key_exists('to', $params),
					"'to' not in params but endpoint returned OK! ".
					"This shouldn't happen, fix your tests."
				);

				$queue_resp = QueueUtils::get($this->api, $params['queue_name']);
				APIInterface::assert_success($queue_resp);
			} finally {
				$this->api->logout();
			}

			// Assert that the Slide is where it should be in the Queue.
			$queue_resp_decoded = APIInterface::decode_raw_response($queue_resp);
			if ($params['to'] === Queue::ENDPOS) {
				// Slide moved to end of Queue.
				$tmp = end($queue_resp_decoded->queue->slide_ids);
			} else {
				// Slide moved to an arbitary position in Queue.
				$tmp = $queue_resp_decoded->queue->slide_ids[$params['to']];
			}

			$this->assertEquals(
				$params['slide_id'],
				$tmp,
				"Queue wasn't reordered correctly."
			);
		} else {
			$this->api->logout();
		}
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'to' => 0
				],
				FALSE,
				HTTPStatus::OK
			],
			'Nonexistent queue' => [
				[
					'queue_name' => 'aabbcc',
					'to' => 0
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for queue_name parameter' => [
				[
					'queue_name' => 10,
					'to' => 0
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Empty queue_name parameter' => [
				[
					'queue_name' => '',
					'to' => 0
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Missing queue_name parameter' => [
				[
					'to' => 0
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Nonexistent slide' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'slide_id' => 'aabbcc',
					'to' => 0
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for slide_id parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'slide_id' => 10,
					'to' => 0
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Empty slide_id parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'slide_id' => '',
					'to' => 0
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Missing slide_id parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'to' => 0
				],
				TRUE,
				HTTPStatus::BAD_REQUEST
			],
			'to parameter < -1' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'to' => -2
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'to parameter == -1' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'to' => -1
				],
				FALSE,
				HTTPStatus::OK
			],
			'to parameter > queue length' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'to' => 100
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'to parameter == queue length' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'to' => 1
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Missing to parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for to parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'to' => FALSE
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			]
		];
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[
				'queue_name' => self::TEST_QUEUE_NAME,
				'slide_id' => $this->slide_id,
				'to' => 0
			],
			[]
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/queue_reorder.schema.json'
		);

		$this->api->logout();
	}

	public function tearDown(): void {
		$this->api->login('admin', 'admin');

		// Remove the testing queue.
		APIInterface::assert_success(
			QueueUtils::remove(
				$this->api,
				self::TEST_QUEUE_NAME
			),
			'Failed to remove initial queue.',
			[$this->api, 'logout']
		);

		$this->api->logout();
	}
}
