#!/usr/bin/env python3

# Determine dependency trees for SCSS files based on the @import
# statements in the file. Run ./sassdep.py --help for more information.

import sys;
import re;
import os.path;
import argparse;

pattern = re.compile('\s*@import (.*);\s*');

def check_import_variants(dir, name, ipaths):
	# Try to find a file that corresponds to the filename
	# in an SCSS @import statement in one of the import
	# directories in 'ipaths'. The file is searched from the
	# import directories in the order they are in the 'ipaths'
	# list.

	variants = [
		name,
		'_' + name,
		name + '.scss',
		'_' + name + '.scss'
	];
	for p in ipaths:
		for v in variants:
			if (dir == None):
				tmp = os.path.abspath(os.path.join(p, v));
			else:
				tmp = os.path.abspath(os.path.join(p, dir, v));

			if (os.path.isfile(tmp)):
				return tmp;
	return None;

def guess_file(name, ipaths):
	# Wrapper for check_import_variants().

	if (re.search('/', name)):
		p = os.path.split(name)
		return check_import_variants(p[0], p[1], ipaths);
	else:
		return check_import_variants(None, name, ipaths);
	return None;

def getdeps(file, ipaths, before, depth, opts):
	# Get the dependencies for 'file'.
	#  ipaths = A list of import paths to use.
	#  before = A list of previous imports while recursively
	#           determining the dependencies.
	#  depth  = The current recursion depth.
	#  opts   = The CLI options object return by argparse.parse_args().

	global pattern;

	if (opts.tree):
		print(
			' '*depth*opts.indent +
			re.sub(os.path.join(ipaths[0], ''), '', file)
		);

	imports = before;
	tmp_ipaths = ipaths.copy();
	tmp_ipaths.insert(0, os.path.dirname(file));

	with open(file, 'r') as src:
		ln = True;
		while (ln):
			ln = src.readline();
			tmp = pattern.match(ln);
			if not tmp: continue;

			files = tmp.group(1).translate(
				{
					ord(' '): None,
					ord('\''): None,
					ord('"'): None
				}
			).split(',');

			for f in files:
				fname = guess_file(f, tmp_ipaths);
				if (opts.tree or not fname in imports):
					if not fname:
						raise Exception(
							"Cannot find imported file: " + f
						);
					imports.append(fname);
					imports = getdeps(
						fname,
						ipaths,
						imports,
						depth + 1,
						opts
					);
	return imports;

if (__name__ == '__main__'):
	parser = argparse.ArgumentParser(
		description='Generate dependency trees from SCSS source files.'
	);
	parser.add_argument(
		'-t', '--tree',
		action='store_true',
		help='Print the dependency tree on stdout.'
	);
	parser.add_argument(
		'-i', '--indent',
		action='store',
		type=int,
		default=2,
		help='Dependency tree indentation depth ' +
			'for one level. (default: 2)'
	);
	parser.add_argument(
		'-l', '--list',
		action='store_true',
		help='Print the dependencies as a list on stdout.'
	);
	parser.add_argument('file',
		nargs=1,
		help='The source file to read.'
	);
	parser.add_argument('src_root',
		nargs=1,
		help='The source root of the project.'
	);
	parser.add_argument(
		'import_paths',
		nargs=argparse.REMAINDER,
		help='A list of import paths to use.'
	);
	opts = parser.parse_args(sys.argv[1:]);

	if (opts.tree and opts.list):
		raise Exception(
			"Can't have --tree and --list simultaneously."
		)

	list = ' '.join(getdeps(
		opts.file[0],
		opts.src_root + opts.import_paths,
		[],
		0,
		opts
	));
	if (opts.list): print(list);
