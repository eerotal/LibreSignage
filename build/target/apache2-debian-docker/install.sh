
#!/bin/sh

##
##  LibreSignage install handler for the apache2-debian-docker
##  target. This script invokes 'docker build' to configure and
##  build the Docker image.
##

set -e

. build/scripts/conf.sh
. build/scripts/ldconf.sh

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

echo "[INFO] Args for 'docker build': $args";

docker build -t libresignage:"$LS_VER" $args .
