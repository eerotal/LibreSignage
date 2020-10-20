#!/bin/sh

##
##  Create the symlink pointing to the last generated
##  build configuration.
##

if [ -z "$1" ]; then
	echo "[Error] Empty config path."
	exit 1
fi

mkdir -p "build/link"
rm -f "build/link/last.conf"
ln -sr "`pwd`/$1" "build/link/last.conf"
