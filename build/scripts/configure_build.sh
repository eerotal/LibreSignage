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
	echo '  --ver=VERSION .............. Manually specify a version string to use.'
	echo '  --pass ..................... All options after --pass are passed'
	echo '                               to the target build config script.'
	echo '  --help ..................... Print this message and exit.'
}

PASS_REMAINING=0
PASS=''

while [ $# -gt 0 ]; do
	case "$1" in
		--target=*)
			TARGET="$(get_arg_value "$1")"
			;;
		--ver)
			VERSION="$(get_arg_value "$1")"
			;;
		--pass)
			PASS_REMAINING=1
			;;
		--help)
			script_help
			exit 0
			;;
		*)
			if [ "$PASS_REMAINING" = "1" ]; then
				PASS="$(if [ -z "$PASS" ]; then echo "$1"; else echo "$PASS $1"; fi)"
			else
				echo "[Error] Unknown option '$1'." > /dev/stderr
				echo ''
				script_help
				exit 1
			fi
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
