<?php

require_once(LIBRESIGNAGE_ROOT.'/api/module.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/auth/userquota.php');

class APIRateLimitModule extends APIModule {
	/*
	*  Make sure the caller has rate quota left and use it
	*  if needed. This module must be called after the user
	*  data has been assigned into the endpoint (ie. after
	*  APIAuthModule).
	*/
	public function __construct() {
		parent::__construct();
	}

	public function run(APIEndpoint $e, array $args) {
		assert(
			$e->get_caller() !== NULL,
			new APIException(
				API_E_INTERNAL,
				"User data not loaded. You must call APIAuthModule first."
			)
		);

		$quota = $e->get_caller()->get_quota();
		if ($quota->has_state('api_t_start')) {
			$t = $quota->get_state('api_t_start');
			if (time() - $t >= gtlim('API_RATE_T')) {
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
				API_E_RATE,
				"API rate limited."
			);
		} else {
			$quota->use_quota('api_rate');
		}
		$e->get_caller()->write();
	}
}
