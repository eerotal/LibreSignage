#!/bin/sh

##
##  Start a LibreSignage Docker container.
##

. build/scripts/conf.sh
. build/scripts/ldconf.sh

echo "[INFO] Starting a LibreSignage Docker container."

docker run \
	-d \
	-p 80:80 \
	--mount source=ls_dev_vol,target=/var/www/html/data \
	"libresignage:$LS_VER"
