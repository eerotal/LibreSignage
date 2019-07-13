<?php

use \classes\APITestCase;
use \classes\APIInterface;
use \api\HTTPStatus;

class slide_save extends APITestCase {
	const VALID_PARAMS = [
		'id' => NULL,
		'name' => 'Unit-Test-Slide',
		'index' => 1,
		'duration' => 5000,
		'markup' => 'Test Markup',
		'enabled' => TRUE,
		'sched' => FALSE,
		'sched_t_s' => 0,
		'sched_t_e' => 0,
		'animation' => 1,
		'queue_name' => 'default',
		'collaborators' => [],
		'owner' => NULL,
		'lock' => NULL,
		'assets' => NULL
	];

	private $slide_id = NULL;

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('slide/slide_save.php');
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
			$this->slide_id = APIInterface::decode_raw_response($resp)->id;
		}
	}

	public static function params_provider(): array {

		return [
			'Valid parameters' => [
				'admin',
				'admin',
				self::VALID_PARAMS,
				HTTPStatus::OK
			],
			'Missing id parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['id' => '']),
				HTTPStatus::OK
			],
			'NULL id parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['id' => NULL]),
				HTTPStatus::OK
			],
			'Empty name parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['name' => '']),
				HTTPStatus::BAD_REQUEST
			],
			'Name with invalid chars' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['name' => '/../..']),
				HTTPStatus::BAD_REQUEST
			],
			'Missing name parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['name' => '']),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for name parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['name' => 123]),
				HTTPStatus::BAD_REQUEST
			],
			'Negative index parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['index' => -1]),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for name parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['name' => TRUE]),
				HTTPStatus::BAD_REQUEST
			],
			'Missing name parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['name' => '']),
				HTTPStatus::BAD_REQUEST
			],
			'Negative duration parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['duration' => -1000]),
				HTTPStatus::BAD_REQUEST
			],
			'Too big duration parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['duration' => 1000000]),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for duration parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['name' => TRUE]),
				HTTPStatus::BAD_REQUEST
			],
			'Missing duration parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['name' => 5000]),
				HTTPStatus::BAD_REQUEST
			],
			'Empty markup parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['markup' => '']),
				HTTPStatus::OK
			],
			'Wrong type for markup parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['markup' => 123]),
				HTTPStatus::BAD_REQUEST
			],
			'Missing markup parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['markup' => '']),
				HTTPStatus::BAD_REQUEST
			],
			'Missing enabled parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['enabled' => '']),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for enabled parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['enabled' => 123]),
				HTTPStatus::BAD_REQUEST
			],
			'Missing sched parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['sched' => '']),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for sched parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['sched' => 123]),
				HTTPStatus::BAD_REQUEST
			],
			'Missing sched_t_s parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['sched_t_s' => '']),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for sched_t_s parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['sched_t_s' => TRUE]),
				HTTPStatus::BAD_REQUEST
			],
			'Negative sched_t_s parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['sched_t_s' => -1000]),
				HTTPStatus::BAD_REQUEST
			],
			'Missing sched_t_e parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['sched_t_e' => '']),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for sched_t_e parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['sched_t_e' => TRUE]),
				HTTPStatus::BAD_REQUEST
			],
			'Negative sched_t_e parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['sched_t_e' => -1000]),
				HTTPStatus::BAD_REQUEST
			],
			'Missing animation parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['animation' => 0]),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for animation parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['animation' => 'aaa']),
				HTTPStatus::BAD_REQUEST
			],
			'Negative animation parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['animation' => -1]),
				HTTPStatus::BAD_REQUEST
			],
			'Missing collaborators parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['collaborators' => []]),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for collaborators parameter' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['collaborators' => 123]),
				HTTPStatus::BAD_REQUEST
			],
			'Wrong type for collaborators array item' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['collaborators' => [123]]),
				HTTPStatus::BAD_REQUEST
			],
			'Correct type for collaborators array item' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['collaborators' => ['user']]),
				HTTPStatus::OK
			],
			'Invalid username in collaborators array' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['collaborators' => ['nouser']]),
				HTTPStatus::BAD_REQUEST
			],
			'Empty collaborators array' => [
				'admin',
				'admin',
				\array_merge(self::VALID_PARAMS, ['collaborators' => []]),
				HTTPStatus::OK
			],
			'Missing owner parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['owner' => '']),
				HTTPStatus::OK
			],
			'Missing lock parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['lock' => '']),
				HTTPStatus::OK
			],
			'Missing assets parameter' => [
				'admin',
				'admin',
				\array_diff_key(self::VALID_PARAMS, ['assets' => '']),
				HTTPStatus::OK
			],
			'User not in editor or admin groups tries to create a slide' => [
				'display',
				'display',
				self::VALID_PARAMS,
				HTTPStatus::UNAUTHORIZED
			],
			'Slide owner saves slide' => [
				'admin',
				'admin',
				array_merge(self::VALID_PARAMS, ['id' => '1']),
				HTTPStatus::UNAUTHORIZED
			],
			'Non slide owner tries to save slide' => [
				'user',
				'user',
				array_merge(self::VALID_PARAMS, ['id' => '1']),
				HTTPStatus::UNAUTHORIZED
			],
			'User not in editor or admin groups tries to save slide' => [
				'display',
				'display',
				array_merge(self::VALID_PARAMS, ['id' => '1']),
				HTTPStatus::UNAUTHORIZED
			]
		];
	}

	public function test_allow_collaborators_to_edit_slides() {
		// Create a slide for testing.
		$this->api->login('admin', 'admin');
		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			self::VALID_PARAMS,
			[],
			TRUE
		);
		if ($resp->getStatusCode() !== HTTPStatus::OK) {
			throw new Exception(
				'Failed to create slide for testing: '.
				(string) $resp->getBody()
			);
		}
		$this->slide_id = APIInterface::decode_raw_response($resp)->id;
		$this->api->logout();

		// Try to modify the slide as a collaborator.
		$this->api->login('user', 'user');
		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			array_merge(
				self::VALID_PARAMS,
				['markup' => 'Different Test Markup']
			),
			[],
			TRUE
		);
		$this->assert_api_succeeded($resp);
		$this->api->logout();
	}

	public function test_is_response_schema_correct() {
		$resp = $this->call_api_and_check_response_schema(
			self::VALID_PARAMS,
			[],
			dirname(__FILE__).'/schemas/slide_save.schema.json',
			'admin',
			'admin'
		);

		if ($resp->getStatusCode() === HTTPStatus::OK) {
			$slide_id = APIInterface::decode_raw_response($resp)->id;
		}
	}

	public function tearDown(): void {
		if ($this->slide_id !== NULL) {
			$this->api->login('admin', 'admin');

			$resp = $this->api->call_return_raw_response(
				'POST',
				'slide/slide_rm.php',
				['id' => $this->slide_id],
				[],
				TRUE
			);
			$this->slide_id = NULL;

			if ($resp->getStatusCode() !== HTTPStatus::OK) {
				throw new Exception(
					'Failed to cleanup created slide: '.
					(string) $resp->getBody()
				);
			}

			$this->api->logout();
		}
	}
}
