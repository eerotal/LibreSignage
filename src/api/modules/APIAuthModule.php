<?php

namespace api\modules;

use \api\APIEndpoint;
use \api\APIModule;
use \api\HTTPStatus;
use \api\APIException;
use \common\php\auth\Auth;

class APIAuthModule extends APIModule {
	/*
	*  Check authentication and assign the user and session
	*  data into the supplied endpoint.
	*/
	public function run(APIEndpoint $e, array $args) {
		$this->check_args(['cookie_auth'], $args);

		$data = NULL;
		$req = $e->get_request();

		/*
		*  Prevent using cookie auth on POST endpoints because that
		*  would enable CSRF attacks.
		*/
		if ($req->getMethod() === APIEndpoint::M_POST && $args['cookie_auth']) {
			throw new APIException(
				"Prevented cookie authentication on POST endpoint. ".
				"Don't do this, it's dangerous.",
				HTTPStatus::INTERNAL_SERVER_ERROR
			);
		}

		// Check auth token.
		if ($req->headers->get(Auth::TOKEN_HEADER) !== NULL) {
			$data = Auth::verify_token($req->headers->get(Auth::TOKEN_HEADER));
		} else if ($req->cookies->get(Auth::TOKEN_COOKIE) && $args['cookie_auth']) {
			$data = Auth::verify_token($req->cookies->get(Auth::TOKEN_COOKIE));
		} else {
			throw new APIException(
				'No Auth-Token header or token cookie supplied.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		if ($data === NULL) {
			throw new APIException(
				'Not authenticated.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		return [
			'user' => $data['user'],
			'session' => $data['session']
		];
	}
}
