<?php

namespace api\endpoint\slide\asset;

use \classes\APITestCase;
use \classes\SlideUtils;
use \classes\APIInterface;
use \common\php\JSONUtils;
use \api\HTTPStatus;
use \GuzzleHttp\Psr7\MultipartStream;

class slide_upload_asset extends APITestCase {
	const TEST_SLIDE_ID   = '1';
	const TEST_ASSET_PATH = 'tests/tmp/test.png';

	private $upload_success = FALSE;

	use \traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('slide/asset/slide_upload_asset.php');
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(
		string $user,
		string $pass,
		MultipartStream $stream,
		int $error
	) {
		$this->api->login($user, $pass);
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
				'display',
				'display',
				new MultipartStream(
					(function() use ($valid_ms_contents) {
						$valid_ms_contents[0]['contents'] = '{}';
						return $valid_ms_contents;
					})()
				),
				HTTPStatus::BAD_REQUEST
			],
			'Empty id parameter' => [
				'display',
				'display',
				new MultipartStream(
					(function() use ($valid_ms_contents) {
						$valid_ms_contents[0]['contents'] = JSONUtils::encode([
							'id' => '',
						]);
						return $valid_ms_contents;
					})()
				),
				HTTPStatus::BAD_REQUEST
			],
			'Nonexistent id' => [
				'display',
				'display',
				new MultipartStream(
					(function() use ($valid_ms_contents) {
						$valid_ms_contents[0]['contents'] = JSONUtils::encode([
							'id' => 'aabbccddeeff'
						]);
						return $valid_ms_contents;
					})()
				),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for id parameter' => [
				'display',
				'display',
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

	public function tearDown(): void {
		// Cleanup uploaded asset.
		if ($this->upload_success) {
			$this->api->login('admin', 'admin');
			$resp = SlideUtils::remove_asset(
				$this->api,
				self::TEST_SLIDE_ID,
				basename(self::TEST_ASSET_PATH)
			);
			$this->upload_success = FALSE;
			$this->api->logout();

			if ($resp->getStatusCode() !== HTTPStatus::OK) {
				throw new \Exception("Failed to cleanup uploaded asset.");
			}
		}
	}
}
