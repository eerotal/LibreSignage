<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api_error.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/userquota.php');

class APIRateLimitModule extends APIModule {
	/*
	*  Make sure the caller has rate quota left and use it
	*  if needed. This module must be called after the user
	*  data has been assigned into $endpoint (ie. after
	*  APIAuthModule).
	*/
	public function __construct() {
		parent::__construct();
	}

	public function run(APIEndpoint $endpoint) {
		// Use API rate quota  if required.
		if (!$endpoint->requires_quota()) { return; }

		$quota = new UserQuota($endpoint->get_caller());
		if ($quota->has_state_var('api_t_start')) {
			$t = $quota->get_state_var('api_t_start');
			if (time() - $t >= gtlim('API_RATE_T')) {
				// Reset rate quota and time after the cutoff.
				$quota->set_state_var('api_t_start', time());
				$quota->set_quota('api_rate', 0);
			}
		} else {
			// Start counting time.
			$quota->set_state_var('api_t_start', time());
		}
		if (!$quota->use_quota('api_rate')) {
			throw new APIException(
				API_E_RATE,
				"API rate limited."
			);
		}
		$quota->flush();
	}
}
