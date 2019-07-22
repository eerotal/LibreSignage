<?php

namespace libresignage\tests\api\endpoint\slide\asset;

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
		$resp = SlideUtils::upload_asset(
			$this->api,
			self::TEST_SLIDE_ID,
			self::TEST_ASSET_PATH
		);
		$this->api->logout();

		if ($resp->getStatusCode() !== HTTPStatus::OK) {
			throw new \Exception("Failed to upload initial asset.");
		}
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
		$resp = $this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			$user,
			$pass
		);
		if ($resp->getStatusCode() === HTTPStatus::OK) {
			$this->asset_removed = TRUE;
		}
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
				HTTPStatus::BAD_REQUEST
			],
			'Nonexistent id' => [
				'admin',
				'admin',
				[
					'id' => 'aabbccddeeff',
					'name' => basename(self::TEST_ASSET_PATH)
				],
				HTTPStatus::BAD_REQUEST
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

	public function test_asset_is_removed() {
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
		$this->assert_api_succeeded($resp);

		$resp = SlideUtils::get_asset(
			$this->api,
			self::TEST_SLIDE_ID,
			basename(self::TEST_ASSET_PATH)
		);
		$this->assert_api_failed($resp, HTTPStatus::NOT_FOUND);
		$this->asset_removed = TRUE;

		$this->api->logout();
	}

	public function tearDown(): void {
		if (!$this->asset_removed) {
			$this->api->login('admin', 'admin');
			$resp = SlideUtils::remove_asset(
				$this->api,
				self::TEST_SLIDE_ID,
				basename(self::TEST_ASSET_PATH)
			);
			$this->api->logout();

			if ($resp->getStatusCode() !== HTTPStatus::OK) {
				throw new \Exception("Failed to remove initial asset.");
			}
			$this->asset_removed = FALSE;
		}
	}
}
