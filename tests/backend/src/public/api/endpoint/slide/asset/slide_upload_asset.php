<?php

namespace libresignage\tests\backend\api\endpoint\slide\asset;

use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\SlideUtils;
use libresignage\tests\backend\common\classes\APIInterface;
use libresignage\common\php\JSONUtils;
use libresignage\api\HTTPStatus;
use \GuzzleHttp\Psr7\MultipartStream;

class slide_upload_asset extends APITestCase {
	const TEST_SLIDE_ID   = '1';
	const TEST_ASSET_PATH = 'tests/tmp/test.png';

	private $upload_success = FALSE;

	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('slide/asset/slide_upload_asset.php');
	}

	/**
	* Lock the testing slide self::TEST_SLIDE_ID.
	*/
	public function setup_lock_slide() {
		APIInterface::assert_success(SlideUtils::lock(
			$this->api,
			self::TEST_SLIDE_ID
		), 'Failed to lock slide for testing.', [$this, 'abort']);
	}

	/**
	* Fuzz parameters passed to the endpoint.
	*
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(
		string $user,
		string $pass,
		MultipartStream $stream,
		int $error
	) {
		$this->api->login($user, $pass);
		$this->setup_lock_slide();

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$stream,
			['Content-Type' => 'multipart/form-data; boundary='.$stream->getBoundary()],
			TRUE
		);
		$this->upload_success = ($resp->getStatusCode() === HTTPStatus::OK);
		$this->api->logout();

		$this->assert_api_failed($resp, $error);
	}

	public static function params_provider(): array {
		$valid_ms_contents = [
			[
				'name' => 'body',
				'contents' => JSONUtils::encode(['id' => self::TEST_SLIDE_ID])
			],
			[
				'name' => '0',
				'contents' => fopen(self::TEST_ASSET_PATH, 'r'),
				'filename' => basename(self::TEST_ASSET_PATH)
			]
		];

		return [
			'Valid parameters' => [
				'admin',
				'admin',
				new MultipartStream($valid_ms_contents),
				HTTPStatus::OK
			],
			'Missing id parameter' => [
				'admin',
				'admin',
				new MultipartStream(
					(function() use ($valid_ms_contents) {
						$valid_ms_contents[0]['contents'] = '{}';
						return $valid_ms_contents;
					})()
				),
				HTTPStatus::BAD_REQUEST
			],
			'Empty id parameter' => [
				'admin',
				'admin',
				new MultipartStream(
					(function() use ($valid_ms_contents) {
						$valid_ms_contents[0]['contents'] = JSONUtils::encode([
							'id' => '',
						]);
						return $valid_ms_contents;
					})()
				),
				HTTPStatus::NOT_FOUND
			],
			'Nonexistent id' => [
				'admin',
				'admin',
				new MultipartStream(
					(function() use ($valid_ms_contents) {
						$valid_ms_contents[0]['contents'] = JSONUtils::encode([
							'id' => 'aabbccddeeff'
						]);
						return $valid_ms_contents;
					})()
				),
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for id parameter' => [
				'admin',
				'admin',
				new MultipartStream(
					(function() use ($valid_ms_contents) {
						$valid_ms_contents[0]['contents'] = JSONUtils::encode([
							'id' => TRUE
						]);
						return $valid_ms_contents;
					})()
				),
				HTTPStatus::BAD_REQUEST
			]
		];
	}

	/**
	* Test that assets can't be uploaded without locking the slide first.
	*/
	public function test_asset_uploading_not_allowed_on_unlocked_slide() {
		$this->api->login('admin', 'admin');

		$stream = new MultipartStream([
			[
				'name' => 'body',
				'contents' => JSONUtils::encode(['id' => self::TEST_SLIDE_ID])
			],
			[
				'name' => '0',
				'contents' => fopen(self::TEST_ASSET_PATH, 'r'),
				'filename' => basename(self::TEST_ASSET_PATH)
			]
		]);

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$stream,
			['Content-Type' => 'multipart/form-data; boundary='.$stream->getBoundary()],
			TRUE
		);

		$this->upload_success = ($resp->getStatusCode() === HTTPStatus::OK);
		$this->api->logout();

		$this->assert_api_failed($resp, HTTPStatus::FAILED_DEPENDENCY);
	}

	/**
	* Cleanup uploaded assets and release slide locks.
	*/
	public function tearDown(): void {
		$this->api->login('admin', 'admin');

		if ($this->upload_success) {
			APIInterface::assert_success(SlideUtils::lock(
				$this->api,
				self::TEST_SLIDE_ID
			), 'Failed to lock test slide.', [$this->api, 'logout']);

			APIInterface::assert_success(SlideUtils::remove_asset(
				$this->api,
				self::TEST_SLIDE_ID,
				basename(self::TEST_ASSET_PATH)
			), 'Failed to cleanup uploaded asset.', [$this->api, 'logout']);
			$this->upload_success = FALSE;

			APIInterface::assert_success(SlideUtils::release(
				$this->api,
				self::TEST_SLIDE_ID
			), 'Failed to release lock on test slide.', [$this->api, 'logout']);
		}

		$this->api->logout();
	}
}
