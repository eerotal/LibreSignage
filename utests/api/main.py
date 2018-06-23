#!/usr/bin/env python3

from typing import Dict, Any
from resptypes import *;
import unit;
import sys;

HOST = 'http://192.168.1.8';
DATA: Dict[str, Any] = {};

def login_setup(status, req):
	# Postexec function for storing an authentication token in DATA.
	global DATA;
	if (status):
		DATA['Auth-Token'] = req.json()['session']['token'];
	else:
		print("[ERROR] Login failed. Can't continue testing.");
		sys.exit(1);

def login_use():
	# Preexec function for using authentication token in DATA.
	global DATA;
	return {
		'headers_request': DATA
	};

tests = [
	unit.Unit(
		name = "auth_login.php",
		url = HOST + "/api/endpoint/auth/auth_login.php",
		request_method = unit.Unit.METHOD_POST,

		# Pre/Post executed code.
		preexec = lambda: {},
		postexec = login_setup,

		# Request data.
		data_request = {
			'username': 'admin',
			'password': 'admin',
			'who': 'LibreSignage-Unit-Tests',
			'permanent': False
		},
		headers_request = {
			'Content-Type': 'application/json'
		},
		cookies_request = None,

		# Expected response.
		data_expect = {
			"session": RespDict({
				"who": RespStr(None),
				"from": RespStr(None),
				"created": RespInt(None),
				"max_age": RespInt(None),
				"permanent": RespBool(None),
				"token": RespStr(None)
			}),
			"error": RespInt(0)
		},
		headers_expect = {
			'Date': None,
			'Server': None,
			'Access-Control-Allow-Origin': None,
			'Set-Cookie': None,
			'Content-Length': None,
			'Keep-Alive': None,
			'Connection': None,
			'Content-Type': 'application/json'
		}

	),
	unit.Unit(
		name = "auth_get_sessions.php",
		url = HOST + "/api/endpoint/auth/auth_get_sessions.php",
		request_method = unit.Unit.METHOD_GET,

		# Pre/Post executed code.
		preexec = login_use,
		postexec = lambda a, b: None,

		# Request data.
		data_request = {},
		headers_request = {
			'Content-Type': 'application/json'
		},
		cookies_request = None,

		# Expected response.
		data_expect = {
			"sessions": RespDict(None),
			"error": RespInt(0)
		},
		headers_expect = {
			'Date': None,
			'Server': None,
			'Access-Control-Allow-Origin': None,
			'Content-Length': None,
			'Keep-Alive': None,
			'Connection': None,
			'Content-Type': 'application/json'
		}
	),
	unit.Unit(
		name = "auth_logout.php",
		url = HOST + "/api/endpoint/auth/auth_logout.php",
		request_method = unit.Unit.METHOD_POST,

		# Pre/Post executed code.
		preexec = login_use,
		postexec = lambda a, b: None,

		# Request data.
		data_request = {},
		headers_request = {
			'Content-Type': 'application/json'
		},
		cookies_request = None,

		# Expected response.
		data_expect = {
			"error": RespInt(0)
		},
		headers_expect = {
			'Date': None,
			'Server': None,
			'Access-Control-Allow-Origin': None,
			'Content-Length': None,
			'Keep-Alive': None,
			'Connection': None,
			'Content-Type': 'application/json'
		}
	)
];

unit.run_tests(tests);
