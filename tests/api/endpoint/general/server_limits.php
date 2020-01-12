<?php

namespace libresignage\tests\api\endpoint\general;

use \JsonSchema\Validator;
use libresignage\tests\common\classes\APITestCase;
use libresignage\tests\common\classes\APITestUtils;

class server_limits extends APITestCase {
	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('GET');
		$this->set_endpoint_uri('general/server_limits.php');
	}

	public function test_is_response_schema_correct(): void {
		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			NULL
		);
		$this->assert_object_matches_schema(
			$resp,
			dirname(__FILE__).'/schemas/server_limits.schema.json'
		);
	}
}
