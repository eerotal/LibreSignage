<?php

require_once(LIBRESIGNAGE_ROOT.'/api/APIModule.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/auth/auth.php');

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
		if ($req->headers->get(AUTH_TOKEN_HEADER) !== NULL) {
			$data = auth_token_verify($req->headers->get(AUTH_TOKEN_HEADER));
		} else if ($req->cookies->get(AUTH_TOKEN_COOKIE) && $args['cookie_auth']) {
			$data = auth_token_verify($req->cookies->get(AUTH_TOKEN_COOKIE));
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
