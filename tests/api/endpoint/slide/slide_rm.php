<?php

use \classes\APITestCase;
use \classes\APIInterface;
use \api\HTTPStatus;

class slide_rm extends APITestCase {
	private $slide_id = NULL;

	use \traits\TestEndpointNotAuthorizedWithoutLogin;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('slide/slide_rm.php');

		// Create an initial slide that's removed.
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
				"Failed to create initial slide:\n".
				(string) $resp->getBody()
			);
		}
		$this->slide_id = APIInterface::decode_raw_response($resp)->id;

		$this->api->logout();
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

			$resp = $this->api->call_return_raw_response(
				$this->get_endpoint_method(),
				$this->get_endpoint_uri(),
				['id' => $this->slide_id],
				[],
				TRUE
			);
			if ($resp->getStatusCode() !== HTTPStatus::OK) {
				throw new Exception(
					'Failed to remove initial slide: '.
					(string) $resp->getBody()
				);
			}
			$this->slide_id = NULL;

			$this->api->logout();
		}
	}
}
