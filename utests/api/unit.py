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
import re;

class Unit:
	# HTTP methods.
	METHOD_GET: str = "GET";
	METHOD_POST: str = "POST";

	# Response mimetypes.
	MIME_TEXT: str = "text/plain";
	MIME_JSON: str = "application/json";

	resp_mime: str = "";

	def __init__(	self,
			host: str,
			name: str,
			url: str,
			request_method: str,

			preexec: Callable[[], Dict[str, Any]],
			postexec: Callable[[bool, Response], None],

			data_request: Any,
			headers_request: Dict[str, str],
			cookies_request: Any,

			data_expect_strict: bool,
			headers_expect_strict: bool,
			status_expect: int,
			data_expect: Any,
			headers_expect: Dict[str, RespVal]) -> None:

		self.host = host;
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

		self.headers_expect_strict = headers_expect_strict;
		self.data_expect_strict = data_expect_strict;
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
		req_ct = self.get_req_header('Content-Type');
		if (self.request_method == self.METHOD_POST):
			params = {};
			if (req_ct == self.MIME_JSON):
				data = json.dumps(self.data_request);
			else:
				data = self.data_request;
		elif (self.request_method == self.METHOD_GET):
			data = "";
			params = self.data_request;

		# Send the correct request.
		try:
			req = requests.request(
				method = self.request_method,
				url = self.host + self.url,
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

		# Store the response mimetype.
		resp_ct = req.headers['Content-Type'];
		if (not resp_ct or
			re.match('^' + self.MIME_TEXT + '.*', resp_ct)):
			self.resp_mime = self.MIME_TEXT;
		elif (re.match('^' + self.MIME_JSON + '.*', resp_ct)):
			self.resp_mime = self.MIME_JSON;
		else:
			print(
				"Unknown response mimetype: '" + resp_ct +
				"'. Using '" + self.MIME_TEXT + "'."
			);
			self.resp_mime = self.MIME_TEXT;

		# Validate response.
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
			print(json.dumps(
				self.headers_request,
				indent=4
			));

			print(">> Body dump:");
			print(json.dumps(
				self.data_request,
				indent=4
			));

			print("========================\n");
			print("======= Response =======");
			print(">> Status code: " + str(req.status_code));

			print(">> Header dump:");
			print(json.dumps(
				dict(req.headers.items()),
				indent=4
			));

			print(">> Body dump:");
			if (self.resp_mime == self.MIME_JSON):
				try:
					print(json.dumps(
						req.json(),
						indent=4
					));
				except json.decoder.JSONDecodeError:
					print(">>> JSON decoding " +
						"failed. Printing " +
						"raw dump.");
					print(req.text);
			elif (self.resp_mime == self.MIME_TEXT):
				print(req.text);
			else:
				raise Exception(
					"Unknown response mime type."
				);

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
		r = req.headers;
		e = self.headers_expect;

		# Check expected header keys.
		if self.headers_expect_strict:
			if not set(r.keys()) == set(e.keys()):
				ret.append(UnitHeaderKeyError(
					r.keys(), e.keys(), True
				));
				return ret;
		else:
			if not (set(r.keys()) & set(e.keys())
						== set(e.keys())):
				ret.append(UnitHeaderKeyError(
					r.keys(), e.keys(), False
				));
				return ret;

		# Check expected header values.
		for k in e.keys():
			if not (e[k].validate(r[k])):
				ret.append(UnitHeaderError(
					k, r[k], e[k]
				));
		return ret;

	def handle_data(self, req: Response) -> List[UnitError]:
		#
		#  Handle response data.
		#
		if (self.resp_mime == self.MIME_JSON):
			return self.handle_json(req);
		elif (self.resp_mime == self.MIME_TEXT):
			return self.handle_text(req);

	def handle_json(self, req: Response) -> List[UnitError]:
		#
		#  Compare the response JSON of 'req' with the
		#  expected JSON response.
		#
		ret: List[UnitError] = [];
		r = None;
		e = self.data_expect;

		# Parse JSON response.
		try:
			r = req.json();
		except ValueError:
			ret.append(UnitDataTypeError("JSON").printerr());
			return ret;

		# Check expected keys.
		if (self.data_expect_strict):
			if not (set(e.keys()) == set(r.keys())):
				ret.append(UnitJsonDataKeyError(
					r.keys(), e.keys(), True
				));
				return ret;
		else:
			if not (set(e.keys()) & set(r.keys())
						== set(e.keys())):
				ret.append(UnitJsonDataKeyError(
					r.keys(), e.keys(), False
				));
				return ret;

		# Check expected data.
		for k in e.keys():
			if not e[k].validate(r[k]):
				ret.append(UnitJsonDataError(
					k, r[k], e[k]
				));
		return ret;

	def handle_text(self, req: Response) -> List[UnitError]:
		if self.data_expect == None:
			return [];
		elif not self.data_expect.validate(req.text):
			return [UnitTextDataError(
				req.text,
				self.data_expect
			)];
		return [];

def run_tests(tests: list) -> None:
	for t in tests:
		t.run();
