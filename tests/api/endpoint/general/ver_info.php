<?php

use JsonSchema\Validator;
use classes\APITestCase;
use classes\APITestUtils;

class ver_info extends APITestCase {
	use traits\TestIsResponseCode200;
	use traits\TestIsResponseContentTypeJSON;

	public function setUp(): void {
		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('general/ver_info.php');
		parent::setUp();
	}

	public function test_is_response_schema_correct(): void {
		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri()
		);
		$this->assert_valid_json(
			$resp,
			dirname(__FILE__).'/schemas/ver_info.schema.json'
		);
	}
}
