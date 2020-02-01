#!/bin/sh

#
# Installation script for the apache2-debian-docker target.
#

set -e

. build/scripts/conf.sh
. build/scripts/ldconf.sh
. build/scripts/args.sh

#
# Setup and parse arguments.
#

script_help() {
	echo 'Usage:'
	echo '  make install'
	echo '  ./build/target/apache2-debian-docker/install.sh'
	echo ''
	echo 'Build a LibreSignage Docker image.'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) ........... DESCRIPTION'
	echo '  --config=FILE (last generated) ... Use a specific configuration file.'
	echo '  --help ........................... Print this message and exit.'
}

BUILD_CONFIG=''

while [ $# -gt 0 ]; do
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

#
# Build Docker image.
#

args="--build-arg version=$LS_VER"
args="$args --build-arg logdir=$LOG_DIR"
args="$args --build-arg approot=$CONF_APPROOT"

if [ "$CONF_DEBUG" = "TRUE" ]; then
	args="$args --build-arg debug=y"
fi

if [ "$CONF_FEATURE_IMGTHUMBS" = "TRUE" ]; then
	args="$args --build-arg imgthumbs=y"
fi

if [ "$CONF_FEATURE_VIDTHUMBS" = "TRUE" ]; then
	args="$args --build-arg vidthumbs=y"
fi

echo "[Info] Args for 'docker build': $args";

docker build -t libresignage:"$LS_VER" $args .
