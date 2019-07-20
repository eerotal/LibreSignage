#!/bin/sh

#
# A simple text preprocessor that replaces strings in input files
# with values from the build config.
#
# This script replaces strings of the form '#pre(VARIABLE_NAME)' with
# $VARIABLE_NAME loaded from a build config file.
#

set -e;
. build/scripts/conf.sh
. build/scripts/ldconf.sh;
. build/scripts/args.sh

script_help() {
	echo 'Usage: ./build/scripts/prep.sh [OPTION]... [FILE]'
	echo ''
	echo 'The LibreSignage source preprocessor for replacing constants'
	echo 'in source code with respective build config values.'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) ........... DESCRIPTION'
	echo '  --config=FILE (last generated) ... Use a specific build config.'
	echo '  --help ........................... Print this message and exit.'
}

BUILD_CONFIG=''

while [ $# -gt 1 ]; do
	case "$1" in
		--config=*)
			BUILD_CONFIG="$(get_arg_value "$1")"
			;;
		--help)
			script_help
			exit 0
			;;
		*)
			echo "[Error] Unknown option '$1'." > /dev/stderr
			echo ''
			script_help
			exit 1
			;;
	esac

	set +e
	shift > /dev/null 2>&1
	if [ ! "$?" = "0" ]; then
		set -e
		break
	fi
	set -e
done

load_build_config "$BUILD_CONFIG"

if [ ! -f "$1" ]; then
	echo "[Error] No such file: $1" > /dev/stderr
	exit 1
fi

FILE=`cat "$1"`;
echo "$FILE" | grep -o '#pre(.*)' | while read -r line; do
	VN=`echo "$line" | cut -c6- | rev | cut -c2- | rev`;
	eval FN="\$$VN";
	if [ -z "$FN" ]; then
		echo "[Error] Constant $VN not set." > /dev/stderr;
		exit 1;
	else
		sed -i "s/#pre($VN)/$FN/g" "$1";
	fi
done
