#!/bin/sh

##
##  Load LibreSignage instance config file. If
##  $1 is a valid filepath, load the config from
##  it. Otherwise use the last generated config.
##

set -e

check_config() {
	# Check the required values are set.
	if [ -z "$ICONF_DOCROOT" ]; then
		echo '[ERROR] No document root in config.';
		exit 1;
	elif [ -z "$ICONF_NAME" ]; then
		echo '[ERROR] No server name in config.';
		exit 1;
	fi
}

if [ -n "$1" ]; then
	echo "Load config from '$1'.";
	. "$1";
	check_config;
else
	if [ -f "build/link/last.sh" ]; then
		echo "Load config from '`readlink -f "build/link/last.sh"`'";
		. "build/link/last.sh";
		check_config;
	else
		echo "[ERROR] Instance config doesn't exist. Have you run 'make configure'?";
		exit 1;
	fi
fi
