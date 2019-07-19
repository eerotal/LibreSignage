#!/bin/sh

#
# Run a build configuration script for a specific target.
#

set -e

. build/scripts/args.sh

PASS=''
TARGET=''
TARGET_SCRIPT=''

script_help() {
	echo 'Usage: ./build/scripts/configure_build.sh [OPTION]...'
	echo ''
	echo 'Run the build configuration script for a build target.'
	echo 'You must pass at least the --target option.'
	echo ''
	echo 'Any options not recognized by this script are passed to'
	echo 'the target configuration script.'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) ..... DESCRIPTION'
	echo '  --target=TARGET ............ The target name to use.'
}

while [ $# -gt 0 ]; do
	case "$1" in
		--target=*)
			TARGET="$(get_arg_value "$1")"	
			;;
		*)
			PASS="$(if [ -z "$PASS" ]; then echo "$1"; else echo "$PASS $1"; fi)"
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

if [ -z "$TARGET" ]; then
	echo "[Error] Please specify a build target using '--target=TARGET'." > /dev/stderr
	exit 1
fi

TARGET_SCRIPT="build/target/$TARGET/build_config.sh"

if [ ! -f "$TARGET_SCRIPT" ]; then
	echo "[Error] Target '$TARGET' doesn't exist." > /dev/stderr
	exit 1
fi

echo "[Info] Configuring for '$TARGET'."
echo "[Info] Pass to build config generator: '$PASS'"

sh "$TARGET_SCRIPT" $PASS
