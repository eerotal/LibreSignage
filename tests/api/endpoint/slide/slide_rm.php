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
		$resp = APIInterface::assert_success(SlideUtils::save(
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
			['default'],
			[]
		), 'Failed to create initial slide.', [$this, 'abort']);
		$this->api->logout();

		$this->slide_id = APIInterface::decode_raw_response($resp)->slide->id;
	}

	/**
	* Fuzz request parameters.
	*
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

		$this->api->login($user, $pass);

		/*
		* Don't test this for errors so that we can test the slide_rm
		* call even when the slide isn't locked.
		*/
		if (isset($params['id'])) {
			SlideUtils::lock($this->api, $params['id']);
		}

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$params,
			[],
			TRUE
		);
		if (
			$pass_initial_slide_id
			&& $resp->getStatusCode() === HTTPStatus::OK
		) { $this->slide_id = NULL; }

		$this->assert_api_failed($resp, $error);

		$this->api->logout();
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
				HTTPStatus::NOT_FOUND
			],
			'Empty id parameter' => [
				'admin',
				'admin',
				['id' => ''],
				FALSE,
				HTTPStatus::NOT_FOUND
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

	/**
	* Test that the response schema is correct.
	*/
	public function test_is_response_schema_correct() {
		$this->api->login('admin', 'admin');

		$this->assert_api_succeeded(SlideUtils::lock(
			$this->api,
			$this->slide_id
		));

		$resp = $this->api->call_return_raw_response(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			['id' => $this->slide_id],
			[],
			TRUE
		);
		$this->assert_api_succeeded($resp);

		if ($resp->getStatusCode() === HTTPStatus::OK) {
			$this->slide_id = NULL;
		}

		$this->assert_object_matches_schema(
			APIInterface::decode_raw_response($resp),
			dirname(__FILE__).'/schemas/slide_rm.schema.json'
		);

		$this->api->logout();
	}

	/**
	* Test that unlocked slides can't be removed.
	*/
	public function test_removing_unlocked_slides_not_allowed() {
		$this->call_api_and_assert_failed(
			['id' => $this->slide_id],
			[],
			HTTPStatus::FAILED_DEPENDENCY,
			'admin',
			'admin'
		);
	}

	public function tearDown(): void {
		// Make sure the initial slide is removed.
		if ($this->slide_id !== NULL) {
			$this->api->login('admin', 'admin');

			APIInterface::assert_success(SlideUtils::lock(
				$this->api,
				$this->slide_id
			), 'Failed to lock initial slide.', [$this->api, 'logout']);

			APIInterface::assert_success(SlideUtils::remove(
				$this->api,
				$this->slide_id
			), 'Failed to remove initial slide.', [$this->api, 'logout']);
			$this->slide_id = NULL;

			$this->api->logout();
		}
	}
}
