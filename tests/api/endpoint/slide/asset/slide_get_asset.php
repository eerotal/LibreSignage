<?php

use \classes\APITestCase;
use \classes\APIInterface;
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\MultipartStream;
use \common\php\JSONUtils;
use \api\HTTPStatus;

class slide_get_asset extends APITestCase {
	const TEST_SLIDE_ID = '1';
	const TEST_ASSET_PATH = 'tests/tmp/test.png';

	use \traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('slide/asset/slide_get_asset.php');


		$this->api->login('admin', 'admin');
		$ms = new MultipartStream(
			[
				[
					'name' => 'body',
					'contents' => JSONUtils::encode([
						'id' => self::TEST_SLIDE_ID,
						'name' => basename(self::TEST_ASSET_PATH)
					])
				],
				[
					'name' => '0',
					'contents' => fopen(self::TEST_ASSET_PATH, 'r'),
					'filename' => basename(self::TEST_ASSET_PATH)
				]
			]
		);
		$resp = $this->api->call_return_raw_response(
			'POST',
			'slide/asset/slide_upload_asset.php',
			$ms,
			['Content-Type' =>
				'multipart/form-data; boundary='.$ms->getBoundary()],
			TRUE
		);
		$this->api->logout();

		if ($resp->getStatusCode() !== HTTPStatus::OK) {
			throw new \Exception('Failed to upload initial asset.');
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
				HTTPStatus::BAD_REQUEST
			],
			'Nonexistent slide id' => [
				'admin',
				'admin',
				[
					'id' => '11bbcceeddff',
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

		// MIME
		$this->assert_header_exists($resp, 'Content-Type');
		$this->assert_header_matches(
			$resp,
			'Content-Type',
			[mime_content_type(self::TEST_ASSET_PATH)]
		);

		// Size
		$this->assert_header_exists($resp, 'Content-Length');
		$this->assert_header_matches(
			$resp,
			'Content-Length',
			[filesize(self::TEST_ASSET_PATH)]
		);
	}

	public function tearDown(): void {
		$this->api->login('admin', 'admin');
		$resp = $this->api->call_return_raw_response(
			'POST',
			'slide/asset/slide_remove_asset.php',
			[
				'id' => self::TEST_SLIDE_ID,
				'name' => basename(self::TEST_ASSET_PATH)
			],
			[],
			TRUE
		);
		$this->api->logout();

		if ($resp->getStatusCode() !== HTTPStatus::OK) {
			throw new \Exception('Failed to remove initial asset.');
		}
	}
}
