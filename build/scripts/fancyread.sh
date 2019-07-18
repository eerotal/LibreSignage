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
	read -p "$1 [$2]: " ret
	if [ -z "$ret" ]; then
		echo "$2"
	elif [ -n "$3" ]; then
		shift 2
		allowed="$@"
		case $@ in
			*"$ret"*)
				echo "$ret"
				;;
			*)
				echo "[Error] Expected one of: $allowed" > /dev/stderr
				exit 1
				;;
		esac
	fi
	echo "$ret"
}
