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
			name = 'auth_get_sessions.php',
			host = host,
			url = '/api/endpoint/auth/auth_get_sessions.php',
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
				'sessions': RespDict(None),
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
			name = 'auth_session_renew.php',
			host = host,
			url = '/api/endpoint/auth/auth_session_renew.php',
			request_method = unit.Unit.METHOD_POST,

			preexec = f_session_use,
			postexec = f_session_store,

			data_request = {},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'session': RespDict({
					'who': RespStr(
						'LibreSignage-Utests'
					),
					'from': RespRe('.*'),
					'created': RespInt(None),
					'max_age': RespInt(None),
					'permanent': RespBool(False),
					'token': RespRe('.*')
				}),
				'error': RespInt(0)
			},
			headers_expect = {
				'Access-Control-Allow-Origin': RespRe(
					'.*'
				),
				'Set-Cookie': RespRe('.*'),
				'Content-Type': RespRe(
					'application/json.*'
				)
			}
		),
		unit.Unit(
			name = 'auth_logout_other.php',
			host = host,
			url = '/api/endpoint/auth/auth_logout_other.php',
			request_method = unit.Unit.METHOD_POST,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
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

