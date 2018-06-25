#!/usr/bin/env python3

from typing import Callable, Dict, Any, List;
from resptypes import *;
import unit;
from requests import Response;

tests: List[unit.Unit] = [];

def setup(host: str,
	f_session_use: Callable[[], Dict[str, Any]],
	f_session_store: Callable[[bool, Response], None]) -> None:

	global tests;
	tests = [
		unit.Unit(
			name = 'api_err_codes.php',
			host = host,
			url = '/api/endpoint/general/api_err_codes.php',
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {},
			headers_request = {},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'codes': RespDict(None),
				'error': RespInt(0)
			},
			headers_expect = {
				'Access-Control-Allow-Origin': RespRe(
					'.*'
				),
				'Content-Type': RespRe(
					'application/json.*'
				)
			}
		),
		unit.Unit(
			name = 'api_err_msgs.php',
			host = host,
			url = '/api/endpoint/general/api_err_msgs.php',
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {},
			headers_request = {},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'messages': RespDict(None),
				'error': RespInt(0)
			},
			headers_expect = {
				'Access-Control-Allow-Origin': RespRe(
					'.*'
				),
				'Content-Type': RespRe(
					'application/json.*'
				)
			}
		),
		unit.Unit(
			name = 'library_licenses.php',
			host = host,
			url = '/api/endpoint/general/library_licenses.php',
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {},
			headers_request = {},
			cookies_request = None,

			data_expect_strict = False,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = RespRe('.*'),
			headers_expect = {
				'Access-Control-Allow-Origin': RespRe(
					'.*'
				),
				'Content-Type': RespRe('text/plain.*')
			}
		),
		unit.Unit(
			name = 'libresignage_license.php',
			host = host,
			url = '/api/endpoint/general/libresignage_license.php',
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {},
			headers_request = {},
			cookies_request = None,

			data_expect_strict = False,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = RespRe('.*'),
			headers_expect = {
				'Access-Control-Allow-Origin': RespRe(
					'.*'
				),
				'Content-Type': RespRe('text/plain.*')
			}
		),
		unit.Unit(
			name = 'server_limits.php',
			host = host,
			url = '/api/endpoint/general/server_limits.php',
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {},
			headers_request = {},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'limits': RespDict(None),
				'error': RespInt(0)
			},
			headers_expect = {
				'Access-Control-Allow-Origin': RespRe(
					'.*'
				),
				'Content-Type': RespRe(
					'application/json.*'
				)
			}
		)
	];
