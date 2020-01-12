#!/bin/sh

set -e

#
# Load a LibreSignage build configuration file. A file path can be
# passed in $1. If no file is passed, the last generated config file
# is loaded from build/link/last.sh.
#
# $1 = A config file path or unset for the last generated config.
#
load_build_config() {
	if [ -n "$1" ]; then
		if [ -f "$1" ]; then
			. "$1"
		else
			echo "[Error] No such file: $1" > /dev/stderr
			exit 1
		fi
	else
		if [ -f "build/link/last.conf" ]; then
			. "build/link/last.conf"
		else
			echo "[Error] No build config found." > /dev/stderr
			exit 1
		fi
	fi
}
