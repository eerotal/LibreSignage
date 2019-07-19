#!/bin/sh

#
# Execute the installation script for a build target.
#


set -e
. build/scripts/conf.sh
. build/scripts/ldconf.sh
. build/scripts/args.sh

script_help() {
	echo 'Usage: ./build/scripts/install.sh [OPTION]...'
	echo ''
	echo 'Run the installation script for a target.'
	echo 'The target name is loaded from an existing build config file.'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) ..... DESCRIPTION'
	echo '  --config=FILE .............. Use a specific build config file.'
}

BUILD_CONFIG=''

while [ $# -gt 0 ]; do
	case "$1" in
		--config=*)
			BUILD_CONFIG="$(get_arg_value "$1")"
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

sh "build/target/${CONF_TARGET:?}/install.sh" "$@"
