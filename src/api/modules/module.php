<?php

/*
*  Base module class for API modules used in the API.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/error.php');

abstract class APIModule {
	public function __construct() {}
	abstract function run(APIEndpoint $endpoint);
}
