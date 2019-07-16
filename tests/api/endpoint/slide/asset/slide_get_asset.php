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
