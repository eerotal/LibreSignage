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
	echo 'All options after the --pass option are passed to the target'
	echo 'installation script. The --config option is also always passed'
	echo 'to the target installation script.'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) ..... DESCRIPTION'
	echo '  --config=FILE .............. Use a specific build config file.'
	echo '  --pass ..................... All arguments after --pass are passed'
	echo '                               to the target installation script.'
	echo '  --help ......................Print this message and exit.'
}

BUILD_CONFIG=''
PASS_REMAINING=0
PASS=''

while [ $# -gt 0 ]; do
	if [ "$PASS_REMAINING" = "1" ]; then
		PASS="$(if [ -z "$PASS" ]; then echo "$1"; else echo "$PASS $1"; fi)"
	else
		case "$1" in
			--config=*)
				BUILD_CONFIG="$(get_arg_value "$1")"
				PASS="--config=$BUILD_CONFIG"
				;;
			--pass)
				PASS_REMAINING=1
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
	fi

	set +e
	shift > /dev/null 2>&1
	if [ ! "$?" = "0" ]; then
		set -e
		break
	fi
	set -e
done

load_build_config "$BUILD_CONFIG"

echo "[Info] Pass to target installation script: '$PASS'."

./build/target/${CONF_TARGET:?}/install.sh $PASS
