#!/bin/sh

##
##  Select a target and execute the build config generator for that
##  target. You must pass the target name [TARGET] by using
##  --target [TARGET] when executing this script. Any additional
##  arguments are passed to the build config generator script.
##

set -e

pass=''
target=''

while [ $# -gt 0 ]; do
	case "$1" in
		'--target')
			if [ ! -z "$2" ]; then
				shift
				server_target="$1"
				shift
			else
				echo "Expected target name after '--target'."
				exit 1
			fi
			;;
		*)
			if [ -z "$pass" ]; then
				pass=$1
			else
				pass="$pass $1"
			fi
			shift
			;;
	esac
done

if [ -z "$server_target" ]; then
	echo "[Error] Specify a build target using '--target [target]'."
	exit 1
fi

echo "[INFO] Configuring for '$server_target'."
echo "[INFO] Pass to build config generator: '$pass'"

."/build/target/"$server_target"/build_config.sh" $pass
