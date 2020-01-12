#!/bin/sh

#
# Argument parsing functions for shell scripts.
#

#
# Get the value of an argument of the form --arg=value
#
# $1 = The --arg=value string.
#
get_arg_value() {
	if [ -z "$1" ]; then
		echo "[Error] Expected an argument in '\$1'." > /dev/stderr
		exit 1
	elif [ -z "$(echo "$1" | grep '=')" ]; then
		echo "[Error] Expected a value for '$1'." > /dev/stderr
		exit 1
	fi
	echo "$1" | cut -d'=' -f2-
}
