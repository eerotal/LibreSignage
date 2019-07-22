#!/bin/sh

#
# Read user input from STDIN. If the user input is empty,
# echo a default value. An arbitary number of allowed values
# can also be specified.
#
# $1  = Prompt string. The default value is added to this.
# $2  = Default value.
#
# All remaining arguments are considered allowed values.
#
fancyread() {
	prompt="$1"
	default="$2"
	tmp=""
	ret=""

	shift 2

	if [ -n "$1" ]; then
		tmp=" ($(echo "$@" | sed 's: :/:g'))"
	fi

	read -p "$prompt$tmp [default: $default]: " ret
	if [ -z "$ret" ]; then
		echo "$default"
	elif [ -n "$1" ]; then
		case "$@" in
			*"$ret"*)
				echo "$ret"
				;;
			*)
				echo "[Error] Expected one of: $@" > /dev/stderr
				exit 1
				;;
		esac
	fi
	echo "$ret"
}
