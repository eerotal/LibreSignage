#!/usr/bin/env python3

from typing import Dict, Any
from resptypes import *;
import unit;
import sys;
import importlib;
from os import listdir;
from os.path import isfile, join, splitext;

UPATH = 'utests/api/units';
HOST = 'http://192.168.1.8';
DATA: Dict[str, Any] = {};

def session_store(status, req):
	# Postexec function for storing an authentication token in DATA.
	global DATA;
	if (status):
		DATA['Auth-Token'] = req.json()['session']['token'];
	else:
		print("[ERROR] Session store failed." +
			"Can't continue testing.");
		sys.exit(1);

def session_use():
	# Preexec function for using an authentication token from DATA.
	global DATA;
	return { 'headers_request': DATA };

if __name__ == '__main__':
	queues = [
		q for q in listdir(UPATH)
			if isfile(join(UPATH, q))
			if not q[0] == '.'
	];
	queues = sorted(queues, key=lambda x: x.split('_')[0]);
	for q in queues:
		print("[INFO] Loading '" + q + "'.");
		mod = importlib.import_module(
			'units.' + splitext(q)[0]
		);
		mod.setup(HOST, session_use, session_store);
		unit.run_tests(mod.tests);
