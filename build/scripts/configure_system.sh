#!/bin/sh

#
# Execute the system config generator for a build target.
#

set -e

. build/scripts/conf.sh
. build/scripts/ldconf.sh
. build/scripts/args.sh

script_help() {
	echo 'Usage: ./build/scripts/configure_system.sh [OPTION]...'
	echo ''
	echo 'Run the system configuration generator script for a target.'
	echo 'The target name is loaded from an existing build config file.'
	echo ''
	echo 'The --config option is automatically passed to the target config'
	echo 'generator script..'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) ..... DESCRIPTION'
	echo '  --config=FILE .............. Use a specific build config file.'
	echo '  --pass ..................... All options after --pass are passed'
	echo '                               to the target build config script.'
}

BUILD_CONFIG=''
PASS_REMAINING=0
PASS=''

while [ $# -gt 0 ]; do
	case "$1" in
		--config=*)
			BUILD_CONFIG="$(get_arg_value "$1")"
			;;
		--pass)
			PASS_REMAINING=1
			;;
		*)
			if [ "$PASS_REMAINING" = "1" ]; then
				PASS="$(if [ -z "$PASS" ]; then echo "$1"; else echo "$PASS $1"; fi)"
			else
				echo "[Warning] Unknown option '$1'. Ignoring." > /dev/stderr
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

load_build_config "$BUILD_CONFIG"

echo "[Info] Pass to target system config generator: --config='$BUILD_CONFIG' $PASS"

sh "build/target/${CONF_TARGET:?}/system_config.sh" --config="$BUILD_CONFIG" $PASS
