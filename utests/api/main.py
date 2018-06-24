#!/usr/bin/env python3

from typing import Dict, Any
from resptypes import *;
import unit;
import sys;

HOST = 'http://192.168.1.8';
DATA: Dict[str, Any] = {};

def session_store(status, req):
	# Postexec function for storing an authentication token in DATA.
	global DATA;
	if (status):
		DATA['Auth-Token'] = req.json()['session']['token'];
	else:
		print("[ERROR] Session store failed." +
			"Can't continue testing.");
		sys.exit(1);

def session_use():
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
		postexec = session_store,

		# Request data.
		data_request = {
			'username': 'admin',
			'password': 'admin',
			'who': 'LibreSignage-Utests',
			'permanent': False
		},
		headers_request = {
			'Content-Type': 'application/json'
		},
		cookies_request = None,

		# Expected response.
		status_expect = 200,
		data_expect = {
			"session": RespDict({
				"who": RespStr('LibreSignage-Utests'),
				"from": RespStr(None),
				"created": RespInt(None),
				"max_age": RespInt(None),
				"permanent": RespBool(False),
				"token": RespStr(None)
			}),
			"error": RespInt(0)
		},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Set-Cookie': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespStr('application/json')
		}

	),
	unit.Unit(
		name = "auth_get_sessions.php",
		url = HOST + "/api/endpoint/auth/auth_get_sessions.php",
		request_method = unit.Unit.METHOD_GET,

		# Pre/Post executed code.
		preexec = session_use,
		postexec = lambda a, b: None,

		# Request data.
		data_request = {},
		headers_request = {},
		cookies_request = None,

		# Expected response.
		status_expect = 200,
		data_expect = {
			"sessions": RespDict(None),
			"error": RespInt(0)
		},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespStr('application/json')
		}
	),
	unit.Unit(
		name = "auth_session_renew.php",
		url = HOST + "/api/endpoint/auth/auth_session_renew.php",
		request_method = unit.Unit.METHOD_POST,

		preexec = session_use,
		postexec = session_store,

		data_request = {},
		headers_request = {
			'Content-Type': 'application/json'
		},
		cookies_request = None,

		status_expect = 200,
		data_expect= {
			'session': RespDict(None),
			'error': RespInt(0)
		},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Set-Cookie': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespStr('application/json')
		}
	),
	unit.Unit(
		name = "auth_logout_other.php",
		url = HOST + "/api/endpoint/auth/auth_logout_other.php",
		request_method = unit.Unit.METHOD_POST,

		preexec = session_use,
		postexec = lambda a, b: None,

		data_request = {},
		headers_request = {
			'Content-Type': 'application/json'
		},
		cookies_request = None,

		status_expect = 200,
		data_expect= {
			'error': RespInt(0)
		},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespStr('application/json')
		}
	),
	unit.Unit(
		name = "api_err_codes.php",
		url = HOST + "/api/endpoint/general/api_err_codes.php",
		request_method = unit.Unit.METHOD_GET,

		preexec = session_use,
		postexec = lambda a,b: None,

		data_request = {},
		headers_request = {},
		cookies_request = None,

		status_expect = 200,
		data_expect = {
			'codes': RespDict(None),
			'error': RespInt(0)
		},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespStr('application/json')
		}
	),
	unit.Unit(
		name = "api_err_msgs.php",
		url = HOST + "/api/endpoint/general/api_err_msgs.php",
		request_method = unit.Unit.METHOD_GET,

		preexec = session_use,
		postexec = lambda a,b: None,

		data_request = {},
		headers_request = {},
		cookies_request = None,

		status_expect = 200,
		data_expect = {
			'messages': RespDict(None),
			'error': RespInt(0)
		},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespStr('application/json')
		}
	),
	unit.Unit(
		name = "library_licenses.php",
		url = HOST + "/api/endpoint/general/library_licenses.php",
		request_method = unit.Unit.METHOD_GET,

		preexec = session_use,
		postexec = lambda a,b: None,

		data_request = {},
		headers_request = {},
		cookies_request = None,

		status_expect = 200,
		data_expect = {},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Content-Encoding': RespRe('.*'),
			'Vary': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespRe('text/plain;.*')
		}
	),
	unit.Unit(
		name = "libresignage_license.php",
		url = HOST + "/api/endpoint/general/libresignage_license.php",
		request_method = unit.Unit.METHOD_GET,

		preexec = session_use,
		postexec = lambda a,b: None,

		data_request = {},
		headers_request = {},
		cookies_request = None,

		status_expect = 200,
		data_expect = {},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Content-Encoding': RespRe('.*'),
			'Vary': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespRe('text/plain;.*')
		}
	),
	unit.Unit(
		name = "server_limits.php",
		url = HOST + "/api/endpoint/general/server_limits.php",
		request_method = unit.Unit.METHOD_GET,

		# Pre/Post executed code.
		preexec = session_use,
		postexec = lambda a, b: None,

		# Request data.
		data_request = {},
		headers_request = {},
		cookies_request = None,

		# Expected response.
		status_expect = 200,
		data_expect = {
			"limits": RespDict(None),
			"error": RespInt(0)
		},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespStr('application/json')
		}
	),
	unit.Unit(
		name = "auth_logout.php",
		url = HOST + "/api/endpoint/auth/auth_logout.php",
		request_method = unit.Unit.METHOD_POST,

		# Pre/Post executed code.
		preexec = session_use,
		postexec = lambda a, b: None,

		# Request data.
		data_request = {},
		headers_request = {},
		cookies_request = None,

		# Expected response.
		status_expect = 200,
		data_expect = {
			"error": RespInt(0)
		},
		headers_expect = {
			'Date': RespRe('.*'),
			'Server': RespRe('.*'),
			'Access-Control-Allow-Origin': RespRe('.*'),
			'Content-Length': RespRe('.*'),
			'Keep-Alive': RespRe('.*'),
			'Connection': RespRe('.*'),
			'Content-Type': RespStr('application/json')
		}
	)
];

unit.run_tests(tests);
