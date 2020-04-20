<?php

namespace libresignage\tests\backend\api\endpoint\slide;

use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\api\HTTPStatus;

class slide_get extends APITestCase {
	use \libresignage\tests\backend\common\traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('slide/slide_get.php');
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
			'Valid parameters' => [
				'admin',
				'admin',
				['id' => '1'],
				HTTPStatus::OK
			],
			'Empty slide id' => [
				'admin',
				'admin',
				['id' => ''],
				HTTPStatus::NOT_FOUND
			],
			'Missing id parameter' => [
				'admin',
				'admin',
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Nonexistent slide id' => [
				'admin',
				'admin',
				['id' => 'aabbccddeeff'],
				HTTPStatus::NOT_FOUND
			]
		];
	}

	public function test_is_response_schema_correct() {
		$this->call_api_and_check_response_schema(
			['id' => '1'],
			[],
			dirname(__FILE__).'/schemas/slide_get.schema.json',
			'admin',
			'admin'
		);
	}
}
