#!/bin/sh

set -e
. build/scripts/conf.sh

gen_api_doc() {
	# Get the documentation text from the filepath in $1.
	if [ -z "$1" ]; then
			return '';
	fi

	sed -n '/====>/,/<====/p' "$1"  | \
	sed 's/\s*\*\s\{0,2\}//m'       | \
	sed '/====>/d; /<====/d';
}

gen_api_doc $2 > "$3/`basename --suffix='.php' "$2"`.rst";
