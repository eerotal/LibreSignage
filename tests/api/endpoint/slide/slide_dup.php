<?php

use \classes\APITestCase;
use \classes\APIInterface;
use \api\HTTPStatus;
use \common\php\JSONUtils;

class slide_dup extends APITestCase {
	private $orig_slide_id = NULL;
	private $dup_slide_id = NULL;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('slide/slide_dup.php');

		// Create an initial slide to duplicate.
		$this->api->login('admin', 'admin');

		$resp = $this->api->call_return_raw_response(
			'POST',
			'slide/slide_save.php',
			[
				'id' => NULL,
				'name' => 'Unit-Test-Slide',
				'index' => 0,
				'duration' => 5000,
				'markup' => 'Test Markup',
				'enabled' => TRUE,
				'sched' => FALSE,
				'sched_t_s' => 0,
				'sched_t_e' => 0,
				'animation' => 0,
				'queue_name' => 'default',
				'collaborators' => [],
				'owner' => NULL,
				'lock' => NULL,
				'assets' => NULL
			],
			[],
			TRUE
		);
		if ($resp->getStatusCode() !== HTTPStatus::OK) {
			throw new Exception(
				"Failed to create initial slide: ".
				(string) $resp->getBody()
			);
		}
		$this->orig_slide_id = APIInterface::decode_raw_response($resp)->id;

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
		$resp = $this->call_api_and_assert_failed(
			$params,
			[],
			$error,
			$user,
			$pass
		);

		if ($resp->getStatusCode() === HTTPStatus::OK) {
			$this->dup_slide_id = APIInterface::decode_raw_response(
				$resp
			)->slide->id;
		}
	}

	public static function params_provider(): array {
		return [
			'Valid parameters' => [
				'admin',
				'admin',
				['id' => '1'],
				HTTPStatus::OK
			],
			'Missing id parameter' => [
				'admin',
				'admin',
				[],
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for id parameter' => [
				'admin',
				'admin',
				['id' => TRUE],
				HTTPStatus::BAD_REQUEST
			],
			'Empty id parameter' => [
				'admin',
				'admin',
				['id' => ''],
				HTTPStatus::BAD_REQUEST
			],
			'Editor not in admin or user groups duplicates slide' => [
				'display',
				'display',
				['id' => '1'],
				HTTPStatus::UNAUTHORIZED
			]
		];
	}

	public function test_is_response_schema_correct() {
		$this->dup_slide_id = APIInterface::decode_raw_response(
			$this->call_api_and_check_response_schema(
				['id' => '1'],
				[],
				dirname(__FILE__).'/schemas/slide_dup.schema.json',
				'admin',
				'admin'
			)
		)->slide->id;
	}

	public function tearDown(): void {
		$this->api->login('admin', 'admin');

		// Remove the initial slide.
		$resp = $this->api->call_return_raw_response(
			'POST',
			'slide/slide_rm.php',
			['id' => $this->orig_slide_id],
			[],
			TRUE
		);
		if ($resp->getStatusCode() !== HTTPStatus::OK) {
			throw new Exception(
				'Failed to remove original slide: '.
				(string) $resp->getBody()
			);
		}
		$this->orig_slide_id = NULL;

		// Remove duplicated slide if it was created.
		if ($this->dup_slide_id !== NULL) {
			$resp = $this->api->call_return_raw_response(
				'POST',
				'slide/slide_rm.php',
				['id' => $this->dup_slide_id],
				[],
				TRUE
			);
			if ($resp->getStatusCode() !== HTTPStatus::OK) {
				throw new Exception(
					'Failed to remove duplicated slide: '.
					(string) $resp->getBody()
				);
			}
			$this->dup_slide_id = NULL;
		}

		$this->api->logout();
	}
}
