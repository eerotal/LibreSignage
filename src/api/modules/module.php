<?php

/*
*  Base module class for API modules used in the API.
*/

abstract class APIModule {
	public function __construct() {}
	abstract function run(APIEndpoint $endpoint);
}
