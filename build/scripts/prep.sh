#!/bin/sh

##
## A simple text preprocessor. Replaces constants within text with
## shellscript variable values. This script automatically loads the
## LibreSignage instance config file by using ldconf.sh ie. $1 is
## the path of the config file. If $1 is an empty string, the last
## generated config is used. $2 is the file to process.
##

set -e;
. build/scripts/conf.sh
. build/scripts/ldconf.sh;

FILE=`cat "$2"`;
echo "$FILE" | grep -o '#pre(.*)' | while read -r line; do
	VN=`echo "$line" | cut -c6- | rev | cut -c2- | rev`;
	eval FN="\$$VN";
	if [ -z "$FN" ]; then
		echo "[Error] Constant $VN not set.";
		exit 1;
	else
		sed -i "s/#pre($VN)/$FN/g" "$2";
	fi
done
