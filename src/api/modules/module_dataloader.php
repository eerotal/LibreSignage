<?php

require_once(LIBRESIGNAGE_ROOT.'/api/defs.php');
require_once(LIBRESIGNAGE_ROOT.'/api/modules/module.php');

class APIDataLoaderModule extends APIModule {
	public function __construct() {
		parent::__construct();
	}

	public function run(APIEndpoint $endpoint) {
		$this->load_headers($endpoint);
		$this->load_data($endpoint);
	}

	private function load_headers(APIEndpoint $endpoint) {
		$endpoint->set_header_data(getallheaders());
	}

	private function load_data(APIEndpoint $endpoint) {
		switch($endpoint->get_method()) {
			case API_METHOD['POST']:
				switch ($endpoint->get_request_type()) {
					case API_MIME['application/json']:
						$body_raw = file_get_contents('php://input');
						$endpoint->set_body_data(
							$this->parse_json_data($body_raw)
						);
						$endpoint->set_url_data($_GET);
						break;
					case API_MIME['multipart/form-data']:
						$endpoint->set_url_data($_GET);
						if (
							count($_POST) === 1
							&& array_key_exists('body', $_POST)
						) {
							$endpoint->set_body_data(
								$this->parse_json_data($_POST['body'])
							);
						} else {
							throw new ArgException(
								"Invalid multipart request data. ".
								"Missing 'body' or extra data."
							);
						}
						$endpoint->set_file_data($_FILES);
						break;
					default:
						throw new ArgException("Unknown request type.");
				}
				break;
			case API_METHOD['GET']:
				$endpoint->set_url_data($_GET);
				break;
			default:
				throw new ArgException("Unexpected API method.");
		}
	}

	private function parse_json_data(string $str) {
		// Parse JSON request data.
		if (strlen($str) === 0) {
			$data = [];
		} else {
			$data = json_decode($str, $assoc=TRUE);
			if (
				$data === NULL &&
				json_last_error() != JSON_ERROR_NONE
			) {
				throw new IntException('JSON parsing failed!');
			}
		}
		if ($data === NULL) {
			$data = [];
 		} else if (gettype($data) !== 'array') {
			throw new ArgException(
				'Invalid request data. Expected an  '.
				'array as the root element.'
			);
		}
		return $data;
	}
}
