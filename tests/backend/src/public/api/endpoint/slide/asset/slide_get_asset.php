<?php

namespace libresignage\tests\backend\src\pub\api\endpoint\slide\asset;

use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\APIInterface;
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\MultipartStream;
use libresignage\common\php\JSONUtils;
use libresignage\api\HTTPStatus;
use libresignage\tests\backend\common\classes\SlideUtils;

class slide_get_asset extends APITestCase {
	const TEST_SLIDE_ID = '1';
	const TEST_ASSET_PATH = 'tests/tmp/test.png';

	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('slide/asset/slide_get_asset.php');

		// Upload an initial asset.
		$this->api->login('admin', 'admin');

		APIInterface::assert_success(SlideUtils::lock(
			$this->api,
			self::TEST_SLIDE_ID
		), 'Failed to lock testing slide.', [$this, 'abort']);
		APIInterface::assert_success(SlideUtils::upload_asset(
			$this->api,
			self::TEST_SLIDE_ID,
			self::TEST_ASSET_PATH
		), 'Failed to upload initial asset.', [$this, 'abort']);
		APIInterface::assert_success(SlideUtils::release(
			$this->api,
			self::TEST_SLIDE_ID
		), 'Failed to release initial slide lock.', [$this, 'abort']);

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
	) {
		$this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			$user,
			$pass
		);
	}

	public static function params_provider(): array {
		return [
			'Allowed for group admin' => [
				'admin',
				'admin',
				[
					'id' => self::TEST_SLIDE_ID,
					'name' => basename(self::TEST_ASSET_PATH)
				],
				HTTPStatus::OK
			],
			'Allowed for group user' => [
				'user',
				'user',
				[
					'id' => self::TEST_SLIDE_ID,
					'name' => basename(self::TEST_ASSET_PATH)
				],
				HTTPStatus::OK
			],
			'Allowed for group display' => [
				'display',
				'display',
				[
					'id' => self::TEST_SLIDE_ID,
					'name' => basename(self::TEST_ASSET_PATH)
				],
				HTTPStatus::OK
			],
			'Missing id parameter' => [
				'admin',
				'admin',
				[
					'name' => basename(self::TEST_ASSET_PATH)
				],
				HTTPStatus::BAD_REQUEST
			],
			'Empty id parameter' => [
				'admin',
				'admin',
				[
					'id' => '',
					'name' => basename(self::TEST_ASSET_PATH)
				],
				HTTPStatus::NOT_FOUND
			],
			'Nonexistent slide id' => [
				'admin',
				'admin',
				[
					'id' => '11bbcceeddff',
					'name' => basename(self::TEST_ASSET_PATH)
				],
				HTTPStatus::NOT_FOUND
			],
			'Missing name parameter' => [
				'admin',
				'admin',
				[
					'id' => self::TEST_SLIDE_ID
				],
				HTTPStatus::BAD_REQUEST
			],
			'Empty name parameter' => [
				'admin',
				'admin',
				[
					'id' => self::TEST_SLIDE_ID,
					'name' => ''
				],
				HTTPStatus::NOT_FOUND
			],
			'Nonexistent asset' => [
				'admin',
				'admin',
				[
					'id' => self::TEST_SLIDE_ID,
					'name' => 'nosuchfile.png'
				],
				HTTPStatus::NOT_FOUND
			]
		];
	}

	public function test_received_asset_matches_original() {
		$this->api->login('admin', 'admin');
		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[
				'id' => self::TEST_SLIDE_ID,
				'name' => basename(self::TEST_ASSET_PATH)
			],
			[],
			TRUE
		);
		$this->api->logout();

		$this->assert_header_exists($resp, 'Content-Type');
		$this->assert_header_matches(
			$resp,
			'Content-Type',
			[mime_content_type(self::TEST_ASSET_PATH)]
		);

		$this->assert_header_exists($resp, 'Content-Length');
		$this->assert_header_matches(
			$resp,
			'Content-Length',
			[filesize(self::TEST_ASSET_PATH)]
		);
	}

	public function tearDown(): void {
		// Remove the initial asset.
		$this->api->login('admin', 'admin');

		APIInterface::assert_success(SlideUtils::lock(
			$this->api,
			self::TEST_SLIDE_ID
		), 'Failed to lock test slide.', [$this->api, 'logout']);
		APIInterface::assert_success(SlideUtils::remove_asset(
			$this->api,
			self::TEST_SLIDE_ID,
			basename(self::TEST_ASSET_PATH)
		), 'Failed to remove initial asset.', [$this->api, 'logout']);
		APIInterface::assert_success(SlideUtils::release(
			$this->api,
			self::TEST_SLIDE_ID
		), 'Failed to release lock on test slide.', [$this->api, 'logout']);

		$this->api->logout();
	}
}
