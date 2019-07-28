<?php

namespace libresignage\tests\api\endpoint\slide\asset;

use libresignage\tests\common\classes\APIInterface;
use libresignage\tests\common\classes\APITestCase;
use libresignage\tests\common\classes\SlideUtils;
use libresignage\api\HTTPStatus;

class slide_remove_asset extends APITestCase {
	const TEST_SLIDE_ID = '1';
	const TEST_ASSET_PATH = 'tests/tmp/test.png';

	private $asset_removed = FALSE;

	use \libresignage\tests\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('slide/asset/slide_remove_asset.php');

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
		$this->api->login($user, $pass);
		$this->assert_api_succeeded(SlideUtils::lock(
			$this->api,
			self::TEST_SLIDE_ID
		));

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$params,
			[],
			TRUE
		);
		$this->asset_removed = ($resp->getStatusCode() === HTTPStatus::OK);
		$this->assert_api_failed($resp, $error);

		$this->assert_api_succeeded(SlideUtils::release(
			$this->api,
			self::TEST_SLIDE_ID
		));
		$this->api->logout();
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				'admin',
				'admin',
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
			'Nonexistent id' => [
				'admin',
				'admin',
				[
					'id' => 'aabbccddeeff',
					'name' => basename(self::TEST_ASSET_PATH)
				],
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for id parameter' => [
				'admin',
				'admin',
				[
					'id' => 123,
					'name' => basename(self::TEST_ASSET_PATH)
				],
				HTTPStatus::BAD_REQUEST
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
			'Nonexistent name' => [
				'admin',
				'admin',
				[
					'id' => self::TEST_SLIDE_ID,
					'name' => 'nosuchfile.png'
				],
				HTTPStatus::NOT_FOUND
			],
			'Wrong type for name parameter' => [
				'admin',
				'admin',
				[
					'id' => self::TEST_SLIDE_ID,
					'name' => 123
				],
				HTTPStatus::BAD_REQUEST
			]
		];
	}

	/**
	* Test that the asset is actually removed after calling this endpoint.
	*/
	public function test_asset_is_removed() {
		$this->api->login('admin', 'admin');
		$this->assert_api_succeeded(SlideUtils::lock(
			$this->api,
			self::TEST_SLIDE_ID
		));

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
		$this->asset_removed = ($resp->getStatusCode() === HTTPStatus::OK);
		$this->assert_api_succeeded($resp);

		$resp = SlideUtils::get_asset(
			$this->api,
			self::TEST_SLIDE_ID,
			basename(self::TEST_ASSET_PATH)
		);
		$this->assert_api_failed($resp, HTTPStatus::NOT_FOUND);

		$this->assert_api_succeeded(SlideUtils::release(
			$this->api,
			self::TEST_SLIDE_ID
		));
		$this->api->logout();
	}

	/**
	* Test that assets can't be removed without locking the slide first.
	*/
	public function test_asset_removing_not_allowed_on_unlocked_slides() {
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
		$this->assert_api_failed($resp, HTTPStatus::FAILED_DEPENDENCY);

		$this->api->logout();
	}

	public function tearDown(): void {
		if (!$this->asset_removed) {
			$this->api->login('admin', 'admin');

			APIInterface::assert_success(SlideUtils::lock(
				$this->api,
				self::TEST_SLIDE_ID
			), 'Failed to lock initial slide.', [$this->api, 'logout']);
			APIInterface::assert_success(SlideUtils::remove_asset(
				$this->api,
				self::TEST_SLIDE_ID,
				basename(self::TEST_ASSET_PATH)
			), 'Failed to remove initial asset.', [$this->api, 'logout']);
			APIInterface::assert_success(SlideUtils::release(
				$this->api,
				self::TEST_SLIDE_ID
			), 'Failed to release initial slide.', [$this->api, 'logout']);

			$this->api->logout();
			$this->asset_removed = FALSE;
		}
	}
}
