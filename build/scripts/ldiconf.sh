#!/bin/sh

##
##  Load LibreSignage instance config file. If
##  $1 is a valid filepath, load the config from
##  it. Otherwise use the last generated config.
##

set -e

if [ -n "$1" ]; then
	echo "[INFO] Load config from '$1'."
	. "$1"
else
	if [ -f "build/link/last.sh" ]; then
		echo "[INFO] Load config from"\
			"'`readlink -f "build/link/last.sh"`'"
		. "build/link/last.sh"
	else
		echo "[ERROR] Instance config doesn't exist." \
			"Did you run 'make configure'?"
		exit 1
	fi
fi
