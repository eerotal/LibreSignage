#!/bin/sh

##
## A simple tex preprocessor. Replaces contants within text with
## values defined in a config file. $1 is the path to the config
## file created by configure.sh and $2 is the file to modify.
##

set -e;
. build/scripts/configure.sh;

echo "[INFO] Run preprocessor ('$1' => '$2').";

CONFIG=`cat "$2"`;
echo "$CONFIG" | grep -o '!!BCONST_.*!!' | while read -r line; do
	VN=`echo "$line" | cut -c10- | rev | cut -c3- | rev`;
	eval FN="\$ICONF_$VN";
	if [ -z "$FN" ]; then
		echo "[Error] Constant $VN is not set.";
		exit 1;
	else
		echo "[INFO] BCONF_$VN ==> $FN";
		sed -i "s/!!BCONST_$VN!!/$FN/g" "$2";
	fi
done
