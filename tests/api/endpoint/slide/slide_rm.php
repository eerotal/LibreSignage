<?php

namespace libresignage\tests\api\endpoint\slide;

use libresignage\tests\common\classes\APITestCase;
use libresignage\tests\common\classes\APIInterface;
use libresignage\api\HTTPStatus;
use libresignage\tests\common\classes\SlideUtils;

class slide_rm extends APITestCase {
	private $slide_id = NULL;

	use \libresignage\tests\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('slide/slide_rm.php');

		// Create an initial slide that's removed.
		$this->api->login('admin', 'admin');
		$resp = SlideUtils::save_slide(
			$this->api,
			NULL,
			'Unit-Test-Slide',
			0,
			5000,
			'Test Markup',
			TRUE,
			FALSE,
			0,
			0,
			0,
			'default',
			[]
		);
		$this->api->logout();

		if ($resp->getStatusCode() !== HTTPStatus::OK) {
			throw new Exception("Failed to create initial slide.");
		}
		$this->slide_id = APIInterface::decode_raw_response($resp)->id;
	}

	/**
	* @dataProvider params_provider
	*/
	public function test_fuzz_params(
		string $user,
		string $pass,
		array $params,
		bool $pass_initial_slide_id,
		int $error
	) {
		if ($pass_initial_slide_id) { $params['id'] = $this->slide_id; }

		$resp = $this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			$user,
			$pass
		);

		if (
			$pass_initial_slide_id
			&& $resp->getStatusCode() === HTTPStatus::OK
		) {
			$this->slide_id = NULL;
		}
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				'admin',
				'admin',
				[],
				TRUE,
				HTTPStatus::OK
			],
			'Nonexistent slide id' => [
				'admin',
				'admin',
				['id' => 'aabbccddeeff'],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Empty id parameter' => [
				'admin',
				'admin',
				['id' => ''],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Missing id parameter' => [
				'admin',
				'admin',
				[],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for id parameter' => [
				'admin',
				'admin',
				['id' => 123],
				FALSE,
				HTTPStatus::BAD_REQUEST
			],
			'User user tries to remove slide of user admin' => [
				'user',
				'user',
				[],
				TRUE,
				HTTPStatus::UNAUTHORIZED
			]
		];
	}

	public function test_is_response_schema_correct() {
		$resp = $this->call_api_and_check_response_schema(
			['id' => $this->slide_id],
			[],
			dirname(__FILE__).'/schemas/slide_rm.schema.json',
			'admin',
			'admin'
		);
		if ($resp->getStatusCode() === HTTPStatus::OK) {
			$this->slide_id = NULL;
		}
	}

	public function tearDown(): void {
		// Make sure the initial slide is removed.
		if ($this->slide_id !== NULL) {
			$this->api->login('admin', 'admin');
			$resp = SlideUtils::remove_slide($this->api, $this->slide_id);
			$this->api->logout();

			if ($resp->getStatusCode() !== HTTPStatus::OK) {
				throw new \Exception("Failed to remove initial slide.");
			}
			$this->slide_id = NULL;
		}
	}
}
