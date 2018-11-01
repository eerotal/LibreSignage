<?php

/*
*  LibreSignage API system constants.
*/

// API Endpoint request methods.
const API_METHOD = [
	"GET" => 0,
	"POST" => 1
];

// API Endpoint MIME types.
const API_MIME = [
	"application/json"    => 0,
	"multipart/form-data" => 1,
	"text/plain"          => 2
];

const API_MIME_REGEX_MAP = [
	0 => "/application\/json.*/",
	1 => "/multipart\/form-data.*/",
	2 => "/text\/plain.*/"
];

