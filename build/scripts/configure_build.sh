#!/bin/sh

##
##  Select a build environment and execute the build config
##  generator for that environment. You must pass the environment
##  name [ENV] by using --env [ENV] when executing this script. Any
##  additional arguments are passed to the build config generator
##  script.
##

set -e

pass=''
server_env=''

while [ $# -gt 0 ]; do
	case "$1" in
		'--env')
			if [ ! -z "$2" ]; then
				shift
				server_env="$1"
				shift
			else
				echo "Expected build environment name after '--env'."
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

if [ -z "$server_env" ]; then
	echo "[Error] Specify a build environment."
	exit 1
fi

echo "[INFO] Configuring for '$server_env'."
echo "[INFO] Pass to build config generator: '$pass'"

./"build/env/build_config_generators/$server_env.sh" $pass
