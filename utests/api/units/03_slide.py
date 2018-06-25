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
			name = "slide_data_query.php",
			host = host,
			url = "/api/endpoint/slide/slide_data_query.php",
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'id': '0x1',
			},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'data': RespDict({
					'0x1': RespDict({
						'id': RespStr('0x1')
					}),
					'0x2': RespDict({
						'id': RespStr('0x2')
					})
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
			name = "slide_get.php",
			host = host,
			url = "/api/endpoint/slide/slide_get.php",
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'id': '0x1',
			},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'slide': RespDict({
					'id': RespStr('0x1'),
					'name': RespRe('.*'),
					'index': RespInt(None),
					'time': RespInt(None),
					'markup': RespRe('.*'),
					'owner': RespRe('.*'),
					'enabled': RespBool(None),
					'sched': RespBool(None),
					'sched_t_s': RespInt(None),
					'sched_t_e': RespInt(None),
					'animation': RespInt(None)
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
			name = "slide_list.php",
			host = host,
			url = "/api/endpoint/slide/slide_list.php",
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
				'slides': RespDict(None),
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

