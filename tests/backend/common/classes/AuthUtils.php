<?php

namespace libresignage\tests\backend\common\classes;

use libresignage\tests\backend\common\classes\APIInterface;
use GuzzleHttp\Psr7\Response;
use libresignage\api\HTTPStatus;

final class AuthUtils {
	/**
	* Logout all sessions of the current user except the calling one.
	* This function also cleans up the APIInterface session data to
	* properly keep track of zombie sessions.
	*
	* @param APIInterface $api The APIInterface object to use.
	*
	* @return Response The API response object.
	*/
	public static function logout_other(APIInterface $api): Response {
		$resp = $api->call_return_raw_response(
			'POST',
			'auth/auth_logout_other.php',
			[],
			[],
			TRUE
		);
		if ($resp->getStatusCode() === HTTPStatus::OK) {
			$tmp = $api->get_session();
			$api->pop_sessions_of($api->get_session()->get_username());
			$api->add_session($tmp);
		}
		return $resp;
	}
}
