#!/bin/sh

##
##  Start a LibreSignage Docker container.
##

echo "[INFO] Starting a LibreSignage Docker container."

docker run \
	-d \
	-p 80:80 \
	--mount source=ls_dev_vol,target=/var/www/html/data \
	libresignage
