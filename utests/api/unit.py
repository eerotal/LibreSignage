#!/usr/bin/env python3

#
# Main test unit class.
#

import requests;
from requests.models import Response;
from typing import Callable, Dict, Any, List;
from resptypes import RespVal;
from uniterr import *;
import json;

class Unit:
	# HTTP methods.
	METHOD_GET = "GET";
	METHOD_POST = "POST";

	def __init__(	self,
			name: str,
			url: str,
			request_method: str,

			preexec: Callable[[], Dict[str, Any]],
			postexec: Callable[[bool, Response], None],

			data_request: Any,
			headers_request: Dict[str, Any],
			cookies_request: Any,

			data_expect: Dict[str, RespVal],
			headers_expect: Dict[str, Any]) -> None:

		self.name = name;
		self.url = url;
		self.request_method = request_method;

		self.preexec = preexec;
		self.postexec = postexec;

		self.data_request = data_request;
		self.headers_request = headers_request;
		self.cookies_request = cookies_request;

		self.data_expect = data_expect;
		self.headers_expect = headers_expect;

	def run(self) -> None:
		ret: List[UnitError] = [];
		req = Response();
		data: str = "";
		status = True;

		print("== " + self.name + ": ");

		# Run the preexec function and set the
		# returned values.
		if (self.preexec):
			print("[INFO] Running preexec.");

			tmp = self.preexec();
			if (tmp and 'data_request' in tmp):
				self.headers_request.update(
					tmp['data_request']
				);
			if (tmp and 'headers_request' in tmp):
				self.headers_request.update(
					tmp['headers_request']
				);

		# Convert data to the correct format for POST reqs.
		if (self.get_request_header('Content-Type')
					== 'application/json'):
			data = json.dumps(self.data_request);
		elif (self.get_request_header('Content-Type')
					== 'text/plain'):
			data = self.data_request;

		# Send the correct request.
		if (self.request_method == self.METHOD_POST):
			req = requests.request(
				method = 'POST',
				url = self.url,
				data = data,
				cookies = self.cookies_request,
				headers = self.headers_request
			);
		elif (self.request_method == self.METHOD_GET):
			req = requests.request(
				method = 'GET',
				url = self.url,
				params = self.data_request,
				cookies = self.cookies_request,
				headers = self.headers_request
			);

		ret += self.handle_headers(req);
		ret += self.handle_data(req);

		if (len(ret)):
			# Print errors.
			for err in ret:
				err.printerr();

			print("=== Error information ===\n");

			# Dump request and response.
			print(">> Request URL:");
			print(req.url + "\n");

			print(">> Request header dump:");
			print(json.dumps(self.headers_request, indent=4)
				+ "\n");

			print(">> Request body dump:");
			print(json.dumps(self.data_request, indent=4)
				+ "\n");

			print(">> Response header dump:");
			print(json.dumps(dict(req.headers.items()),
				indent=4) + "\n");

			print(">> Response body dump:");
			print(json.dumps(req.json(), indent=4) + "\n");


			print("=========================")

		# Run the postexec function.
		if (self.postexec):
			print("[INFO] Running postexec.");
			self.postexec(len(ret) == 0, req);

	def get_request_header(self, header):
		if (header in self.headers_request):
			return self.headers_request[header];
		else:
			return None;

	def get_expected_header(self, header):
		if (header in self.headers_expect):
			return self.headers_expect[header];
		else:
			return None;

	def handle_headers(self, req: Response) -> list:
		#
		#  Compare the response headers of 'req' with the
		#  expected headers.
		#
		ret: List[UnitError] = [];
		rhead = req.headers;
		ehead = self.headers_expect;

		# Check expected header keys.
		if not set(rhead.keys()) == set(ehead.keys()):
			ret.append(UnitHeaderKeyError(
				rhead,
				ehead
			));
			return ret;

		# Check expected header values.
		for k in ehead.keys():
			if (not ehead[k] == None and
				not rhead[k] == ehead[k]):
				ret.append(UnitHeaderError(
					k,
					rhead[k],
					ehead[k]
				));
		return ret;

	def handle_data(self, req: Response) -> list:
		#
		#  Handle response data.
		#
		if (req.headers['Content-Type'] == 'application/json'):
			return self.handle_json(req);
		elif (req.headers['Content-Type'] == 'text/plain'):
			return self.handle_text(req);
		return [];

	def handle_json(self, req: Response) -> list:
		#
		#  Compare the response JSON of 'req' with the
		#  expected JSON response.
		#
		ret: List[UnitError] = [];
		rdata = None;
		edata = self.data_expect;

		# Parse JSON response.
		try:
			rdata = req.json();
		except ValueError:
			ret.append(UnitDataTypeError("JSON").printerr());
			return ret;

		# Check expected keys.
		if not (set(edata.keys()) == set(rdata.keys())):
			ret.append(UnitDataKeyError(
				rdata.keys(),
				edata.keys(),
				rdata
			));
			return ret;

		# Check expected data.
		for k in edata.keys():
			if not edata[k].validate(rdata[k]):
				ret.append(UnitDataError(
					k,
					rdata[k],
					edata[k],
					rdata
				));
		return ret;

	def handle_text(self, req: Response) -> list:
		return [];

def run_tests(tests: list) -> None:
	print("[INFO]: Running unit tests.");
	for t in tests:
		t.run();
