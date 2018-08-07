#!/usr/bin/env python3

# Determine dependencies for a SCSS file based on the @import
# statements in the file recursively. $1 is the path of the
# file to read. Include paths can be specified on the CLI after
# the source file as a list of paths.

import sys;
import re;
import os.path;

pattern = re.compile('^@import \'(.*)\';$');

def guess_file(name, import_paths):
	if (name[:1] == '/' and os.path.isfile(name)):
		return name;
	elif not (re.match(name, '.scss') and re.match(name, '/')):
		variants = [
			name + '.scss',
			'_' + name + '.scss'
		];
		for p in import_paths:
			for v in variants:
				if (os.path.isfile(os.path.join(p, v))):
					return os.path.join(p, v);
	return None;

def getdeps(file, ipaths, before):
	global pattern;

	imports = before;

	tmp_ipaths = ipaths;
	tmp_ipaths.append(os.path.dirname(file));

	with open(file, 'r') as src:
		tmp = True;
		while (tmp):
			tmp = pattern.match(src.readline());
			if (tmp):
				fname = guess_file(tmp.group(1), tmp_ipaths);
				if (not fname in imports):
					if not fname:
						raise Exception(
							"Can't find imported file: " + tmp.group(1)
						);
					imports.append(fname);
					imports = getdeps(fname, ipaths, imports);
	return imports;

if (__name__ == '__main__'):
	if (sys.argv[1]):
		print(' '.join(getdeps(sys.argv[1], sys.argv[2:], [])));
