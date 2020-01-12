<?php

namespace libresignage\api\modules;

use libresignage\common\php\Config;
use libresignage\api\APIEndpoint;
use libresignage\api\APIModule;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;

/**
* API module for imposing rate limits on API endpoints.
* This module must be called after checking authentication,
* ie. after APIAuthModule.
*/
class APIRateLimitModule extends APIModule {
	/**
	* Make sure the caller has rate quota left and use it if needed.
	*
	* @see APIModule for argument and return value descriptions.
	*/
	public function run(APIEndpoint $e, array $args) {
		assert(
			array_key_exists('APIAuthModule', $e->get_module_data()),
			new APIException(
				"User data not loaded. You must call APIAuthModule first.",
				HTTPStatus::INTERNAL_SERVER_ERROR
			)
		);

		$user = $e->get_module_data()['APIAuthModule']['user'];
		$quota = $user->get_quota();

		if ($quota->has_state('api_t_start')) {
			$t = $quota->get_state('api_t_start');
			if (time() - $t >= Config::limit('API_RATE_T')) {
				// Reset rate quota and time after the cutoff.
				$quota->set_state('api_t_start', time());
				$quota->set_used('api_rate', 0);
			}
		} else {
			// Start counting time.
			$quota->set_state('api_t_start', time());
		}

		if (!$quota->has_quota('api_rate')) {
			throw new APIException(
				'API rate limited.',
				HTTPStatus::TOO_MANY_REQUESTS
			);
		} else {
			$quota->use_quota('api_rate');
		}
		$user->write();
	}
}
