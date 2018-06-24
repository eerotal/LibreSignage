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
import sys;

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
			headers_request: Dict[str, str],
			cookies_request: Any,

			status_expect: int,
			data_expect: Dict[str, RespVal],
			headers_expect: Dict[str, RespVal]) -> None:

		self.name = name;
		self.url = url;

		if (request_method == self.METHOD_GET or
			request_method == self.METHOD_POST):
			self.request_method = request_method;

		self.preexec = preexec;
		self.postexec = postexec;

		self.data_request = data_request;
		self.headers_request = headers_request;
		self.cookies_request = cookies_request;

		self.status_expect = status_expect;
		self.data_expect = data_expect;
		self.headers_expect = headers_expect;

	def run(self) -> None:
		ret: List[UnitError] = [];
		req = Response();
		status = True;
		data: str = "";
		params: Dict[str, str] = {};

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

		# Convert data to the correct format.
		if (self.request_method == self.METHOD_POST):
			params = {};
			if (self.get_req_header('Content-Type')
						== 'application/json'):
				data = json.dumps(self.data_request);
			else:
				# Default to Content-Type: text/plain.
				data = self.data_request;
		elif (self.request_method == self.METHOD_GET):
			data = "";
			params = self.data_request;

		# Send the correct request.
		try:
			req = requests.request(
				method = self.request_method,
				url = self.url,
				data = data,
				params = params,
				cookies = self.cookies_request,
				headers = self.headers_request
			);
		except requests.exceptions.ConnectionError:
			print(
				"[ERROR] Failed to connect to server. " +
				"Is the server running?"
			);
			sys.exit(1);

		ret += self.handle_status(req);
		ret += self.handle_headers(req);
		ret += self.handle_data(req);

		if (len(ret)):
			# Print errors.
			for err in ret:
				err.printerr();

			print("############ Information ############\n");

			# Dump request and response.
			print("======= Request =======");
			print(">> URL: " + req.url);

			print(">> Header dump:");
			print(json.dumps(self.headers_request, indent=4));

			print(">> Body dump:");
			print(json.dumps(self.data_request, indent=4));

			print("========================\n");
			print("======= Response =======");
			print(">> Status code: " +
				str(req.status_code));

			print(">> Header dump:");
			print(json.dumps(dict(req.headers.items()),
				indent=4));

			print(">> Body dump:");
			print(json.dumps(req.json(), indent=4));
			print("========================\n")

			print("#####################################");


		# Run the postexec function.
		if (self.postexec):
			print("[INFO] Running postexec.");
			self.postexec(len(ret) == 0, req);

	def get_req_header(self, header):
		if (header in self.headers_request):
			return self.headers_request[header];
		else:
			return None;

	def get_expected_header(self, header):
		if (header in self.headers_expect):
			return self.headers_expect[header];
		else:
			return None;

	def handle_status(self, req: Response) -> List[UnitError]:
		if not self.status_expect == req.status_code:
			return [UnitStatusError(
				req.status_code,
				self.status_expect
			)];
		else:
			return [];

	def handle_headers(self, req: Response) -> List[UnitError]:
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
			if not (ehead[k].validate(rhead[k])):
				ret.append(UnitHeaderError(
					k,
					rhead[k],
					ehead[k]
				));
		return ret;

	def handle_data(self, req: Response) -> List[UnitError]:
		#
		#  Handle response data.
		#
		if (req.headers['Content-Type'] == 'application/json'):
			return self.handle_json(req);
		elif (req.headers['Content-Type'] == 'text/plain'):
			return self.handle_text(req);
		return [];

	def handle_json(self, req: Response) -> List[UnitError]:
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

	def handle_text(self, req: Response) -> List[UnitError]:
		return [];

def run_tests(tests: list) -> None:
	print("[INFO]: Running unit tests.");
	for t in tests:
		t.run();
