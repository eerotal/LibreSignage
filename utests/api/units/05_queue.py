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
			name = "queue_create.php",
			host = host,
			url = "/api/endpoint/queue/queue_create.php",
			request_method = unit.Unit.METHOD_POST,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'name': 'utest-queue'
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
			name = "queue_list.php",
			host = host,
			url = "/api/endpoint/queue/queue_list.php",
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
				'queues': RespList(None),
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
			name = "queue_get.php",
			host = host,
			url = "/api/endpoint/queue/queue_get.php",
			request_method = unit.Unit.METHOD_GET,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'name': 'utest-queue'
			},
			headers_request = {
				'Content-Type': 'application/json'
			},
			cookies_request = None,

			data_expect_strict = True,
			headers_expect_strict = False,
			status_expect = 200,
			data_expect = {
				'owner': RespStr('admin'),
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
		unit.Unit(
			name = "queue_remove.php",
			host = host,
			url = "/api/endpoint/queue/queue_remove.php",
			request_method = unit.Unit.METHOD_POST,

			preexec = f_session_use,
			postexec = lambda a, b: None,

			data_request = {
				'name': 'utest-queue'
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

