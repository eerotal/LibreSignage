#!/bin/sh

##
##  Build a new Docker image. By default this script builds
##  a release image, but a development image can be built by
##  passing 'dev' as the first argument.
##

if [ "$1" = "dev" ]; then
	echo "[Info] Build a *development* Docker image."
	FEATURES="imgthumbs,vidthumbs,debug"
else
	echo "[Info] Build a *release* Docker image."
	FEATURES="imgthumbs"
fi

echo "[INFO] Enabled features: $FEATURES"

make configure \
	"TARGET=apache2-debian-docker" \
	"FEATURES=$FEATURES"
make -j`nproc`
make install
