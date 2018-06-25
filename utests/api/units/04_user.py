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
			name = "user_get_current.php",
			host = host,
			url = "/api/endpoint/user/user_get_current.php",
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
				'user': RespDict({
					'user': RespStr('admin'),
					'groups': RespList([
						'admin',
						'editor',
						'display'
					])
				}),
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
			name = "users_get_all.php",
			host = host,
			url = "/api/endpoint/user/users_get_all.php",
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
				'users': RespDict(None),
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
			name = "user_get_quota.php",
			host = host,
			url = "/api/endpoint/user/user_get_quota.php",
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'user': 'admin'
			},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'quota': RespDict(None),
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
			name = "user_create.php",
			host = host,
			url = "/api/endpoint/user/user_create.php",
			request_method = unit.Unit.METHOD_POST,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'user': 'utestuser',
				'groups': ['editor', 'display']
			},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'user': RespDict({
					'name': RespStr('utestuser'),
					'groups': RespList([
						'editor',
						'display'
					]),
					'pass': RespRe('.*'),
				}),
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
			name = "user_save.php",
			host = host,
			url = "/api/endpoint/user/user_save.php",
			request_method = unit.Unit.METHOD_POST,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'user': 'utestuser',
				'pass': None,
				'groups': ['editor']
			},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'user': RespDict({
					'name': RespStr('utestuser'),
					'groups': RespList(['editor'])
				}),
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
			name = "user_get.php",
			host = host,
			url = "/api/endpoint/user/user_get.php",
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'user': 'utestuser'
			},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'user': RespDict({
					'user': RespStr('utestuser'),
					'groups': RespList([
						'editor'
					]),
				}),
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
			name = "user_remove.php",
			host = host,
			url = "/api/endpoint/user/user_remove.php",
			request_method = unit.Unit.METHOD_POST,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'user': 'utestuser'
			},
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

