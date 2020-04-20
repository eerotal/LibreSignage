<?php

namespace libresignage\tests\backend\api\endpoint\general;

use \JsonSchema\Validator;
use libresignage\tests\backend\common\classes\APITestCase;
use libresignage\tests\backend\common\classes\APITestUtils;

class ver_info extends APITestCase {
	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('general/ver_info.php');
	}

	public function test_is_response_schema_correct(): void {
		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			NULL
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/ver_info.schema.json'
		);
	}
}
