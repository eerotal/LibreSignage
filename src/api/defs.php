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

// API type flags.
const API_P_STR          = 0x1;
const API_P_INT          = 0x2;
const API_P_FLOAT        = 0x4;
const API_P_OPT          = 0x8;
const API_P_NULL         = 0x10;
const API_P_BOOL         = 0x20;

// API array type flags.
const API_P_ARR_INT      = 0x40;
const API_P_ARR_STR      = 0x80;
const API_P_ARR_FLOAT    = 0x100;
const API_P_ARR_BOOL     = 0x200;
const API_P_ARR_MIXED    = 0x400;

// API data flags.
const API_P_EMPTY_STR_OK = 0x800;

// API convenience flags.
const API_P_ARR_ANY	= API_P_ARR_STR
                     |API_P_ARR_INT
                     |API_P_ARR_FLOAT
                     |API_P_ARR_BOOL
                     |API_P_ARR_MIXED;

const API_P_ANY     = API_P_STR
                     |API_P_INT
                     |API_P_FLOAT
                     |API_P_OPT
                     |API_P_NULL
                     |API_P_BOOL
                     |API_P_ARR_ANY;

const API_P_UNUSED  = API_P_ANY
                     |API_P_EMPTY_STR_OK
                     |API_P_OPT;
