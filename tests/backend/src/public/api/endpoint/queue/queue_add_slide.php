<?php

namespace libresignage\tests\backend\src\pub\api\endpoint\queue;

use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\APIInterface;
use libresignage\tests\backend\common\classes\QueueUtils;
use libresignage\tests\backend\common\classes\SlideUtils;
use libresignage\common\php\Config;
use libresignage\api\HTTPStatus;

class queue_add_slide extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	const TEST_QUEUE_NAME = 'test_queue';

	private $slide_id = NULL;
	private $slide_added = FALSE;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('queue/queue_add_slide.php');

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

		if ($resp->getStatusCode() === HTTPStatus::OK) {
			$this->slide_added = TRUE;
		}
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'pos' => 0
				],
				FALSE,
				HTTPStatus::OK
			],
			'Nonexistent queue' => [
				[
					'queue_name' => 'aabbcc',
					'pos' => 0
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for queue_name parameter' => [
				[
					'queue_name' => 10,
					'pos' => 0
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Empty queue_name parameter' => [
				[
					'queue_name' => '',
					'pos' => 0
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Missing queue_name parameter' => [
				[
					'pos' => 0
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Nonexistent slide' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'slide_id' => 'aabbcc',
					'pos' => 0
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for slide_id parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'slide_id' => 10,
					'pos' => 0
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Empty slide_id parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'slide_id' => '',
					'pos' => 0
				],
				FALSE,
				HTTPStatus::NOT_FOUND
			],
			'Missing slide_id parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'pos' => 0
				],
				TRUE,
				HTTPStatus::BAD_REQUEST
			],
			'pos parameter < -1' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'pos' => -2
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'pos parameter == -1' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'pos' => -1
				],
				FALSE,
				HTTPStatus::OK
			],
			'pos parameter == -1' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'pos' => -1
				],
				FALSE,
				HTTPStatus::OK
			],
			'pos parameter > queue length' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'pos' => 100
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'pos parameter === queue length' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'pos' => 0
				],
				FALSE,
				HTTPStatus::OK
			],
			'Missing pos parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
				],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for pos parameter' => [
				[
					'queue_name' => self::TEST_QUEUE_NAME,
					'pos' => FALSE
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
				'pos' => 0
			],
			[]
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/queue_add_slide.schema.json'
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

		// Remove the testing slide if it wasn't added to the testing queue.
		if (!$this->slide_added) {
			APIInterface::assert_success(
				SlideUtils::lock(
					$this->api,
					$this->slide_id
				)
			);
			APIInterface::assert_success(
				SlideUtils::remove(
					$this->api,
					$this->slide_id
				)
			);
		}

		$this->api->logout();
	}
}
