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
		try {
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
		} catch (\Exception $e) {
			/**
			 * Remove the testing slide if adding it to the queues
			 * fails. The rest of the cleanup is done by $this->tearDown().
			 */
			SlideUtils::remove($this->api, $this->slide_id);
			throw $e;
		}
		$this->api->logout();
	}

	/**
	 * Test that the endpoint returns the correct HTTP status.
	 *
	 * On HTTP OK test that the Slide was properly removed from the Queue.
	 *
	 * @param array $params      The parameters to pass to the endpoint. The
	 *                           testing Slide ID is automatically added to
	 *                           this array unless slide_id already exists.
	 * @param bool  $no_slide_id If TRUE, the Slide id is not added to $params.
	 * @param int   $error       The HTTP status code to expect.
	 *
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

		// Call the API and assert the HTTP status code.
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
		 * Slide was properly removed.
		 */
		if ($resp->getStatusCode() === HTTPStatus::OK) {
			try {
				assert(
					array_key_exists('slide_id', $params),
					"'slide_id' not in params but endpoint returned OK! ".
					"This shouldn't happen, fix your tests."
				);
				assert(
					array_key_exists('queue_name', $params),
					"'queue_name' not in params but endpoint returned OK! ".
					"This shouldn't happen, fix your tests."
				);

				// Load the Queue via the API.
				$queue_resp = QueueUtils::get($this->api, $params['queue_name']);
				APIInterface::assert_success($queue_resp);

				// Load the removed Slide via the API.
				$slide_resp = SlideUtils::get($this->api, $params['slide_id']);
				APIInterface::assert_success($slide_resp);
			} finally {
				$this->api->logout();
			}

			// Assert that the Slide was removed.
			$queue_resp_decoded = APIInterface::decode_raw_response($queue_resp);
			$this->assertFalse(
				array_key_exists(
					$params['slide_id'],
					$queue_resp_decoded->queue->slide_ids
				),
				"Slide wasn't removed from Queue."
			);

			// Assert that the Slide ref_count was decremented.
			$slide_resp_decoded = APIInterface::decode_raw_response($slide_resp);
			$this->assertEquals(
				1,
				$slide_resp_decoded->slide->ref_count,
				"Slide ref_count wasn't decremented."
			);
		} else {
			$this->api->logout();
		}
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
