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
			name = "auth_login.php",
			host = host,
			url = "/api/endpoint/auth/auth_login.php",
			request_method = unit.Unit.METHOD_POST,

			preexec = lambda: {},
			postexec = f_session_store,

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

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				"session": RespDict({
					"who": RespStr(
						'LibreSignage-Utests'
					),
					"from": RespStr(None),
					"created": RespInt(None),
					"max_age": RespInt(None),
					"permanent": RespBool(False),
					"token": RespStr(None)
				}),
				"error": RespInt(0)
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

		)
	];

