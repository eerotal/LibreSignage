<?php

/*
*  Base filter class for API filters used in the API
*  initialization routines.
*/

abstract class APIFilter {
	public function __construct() {}
	abstract function filter(APIEndpoint $endpoint);
}
