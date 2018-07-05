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
				'id': 1,
			},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'data': RespDict(None),
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
			name = "slide_save.php",
			host = host,
			url = "/api/endpoint/slide/slide_save.php",
			request_method = unit.Unit.METHOD_POST,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'id': None,
				'name': 'LS-Utest-Slide',
				'index': 0,
				'time': 5000,
				'markup': 'Unit testing...',
				'enabled': True,
				'sched': False,
				'sched_t_s': 0,
				'sched_t_e': 0,
				'animation': 1,
				'queue_name': 'default'
			},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'id': RespRe('.*'),
				'name': RespStr('LS-Utest-Slide'),
				'index': RespInt(0),
				'time': RespInt(5000),
				'markup': RespRe('Unit testing...'),
				'enabled': RespBool(True),
				'sched': RespBool(False),
				'sched_t_s': RespInt(0),
				'sched_t_e': RespInt(0),
				'animation': RespInt(1),
				'owner': RespStr('admin'),
				'queue_name': RespStr('default'),
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
			name = "slide_rm.php",
			host = host,
			url = "/api/endpoint/slide/slide_rm.php",
			request_method = unit.Unit.METHOD_POST,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'id': '0x3'
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
					'id': RespRe('0x1'),
					'name': RespRe('.*'),
					'index': RespInt(None),
					'time': RespInt(None),
					'markup': RespRe(None),
					'owner': RespRe('.*'),
					'enabled': RespBool(None),
					'sched': RespBool(None),
					'sched_t_s': RespInt(None),
					'sched_t_e': RespInt(None),
					'animation': RespInt(None),
					'queue_name': RespStr('default')
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
		),
	];

