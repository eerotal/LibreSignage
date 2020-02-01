#!/bin/sh

#
# Installation script for the apache2-debian-docker target.
#
# This script uses docker buildx to build multiarch Docker images and
# automatically pushes the images to Docker Hub. Note that you must
# enable experimental Docker features to use this script for now.
#

set -e

. build/scripts/conf.sh
. build/scripts/ldconf.sh
. build/scripts/args.sh


# Setup and parse arguments.

script_help() {
	echo 'Usage:'
	echo '  make install'
	echo '  ./build/target/apache2-debian-docker/install.sh'
	echo ''
	echo 'Build a LibreSignage Docker image.'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) ............. DESCRIPTION'
	echo '  --config=FILE (last generated) ..... Specify a config file.'
	echo '  --platform=PLATFORMS ............... Specify build platforms.'
	echo '  --tag=TAG .......................... The image tag to use.'
	echo '  --help ............................. Print this message and exit.'
}

BUILD_CONFIG=''
PLATFORMS=''
TAG=''

while [ $# -gt 0 ]; do
	case "$1" in
		--config=*)
			BUILD_CONFIG="$(get_arg_value "$1")"
			;;
		--platform=*)
			PLATFORMS="$(get_arg_value "$1")"
			;;
		--tag=*)
			TAG="$(get_arg_value "$1")"
			;;
		--push)
			PUSH=1
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

if [ -z "$TAG" ]; then
	echo "[Error] No Docker tag specified."
	exit 1
fi

load_build_config "$BUILD_CONFIG"

# Concatenate arguments into one string.

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

if [ -n "$PLATFORMS" ]; then
	args="$args --platform=$PLATFORMS";
fi

args="$args --tag=$TAG --push"

# Login, build and push.

echo "[Info] Args for 'docker buildx build': $args";

if [ -z "$(docker buildx ls | grep lsbuilder)" ]; then
	docker buildx create --driver=docker-container --name lsbuilder
fi
docker buildx use lsbuilder
docker buildx build $args .
