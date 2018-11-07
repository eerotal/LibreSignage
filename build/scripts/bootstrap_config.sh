#!/bin/sh

##
##  Bootstrap the config generation process. This script accepts
##  the environment to use as the first argument and passes any
##  other arguments to the config generation script. If the environment
##  is not specified on the command line, it is asked interactively.
##

if [ -z "$1" ]; then
	read -p "Server environment (Default: apache2-debian): " ICONF_SERVER_ENV
	if [ -z "$ICONF_SERVER_ENV" ]; then
		ICONF_SERVER_ENV="apache2-debian"
	fi
else
	ICONF_SERVER_ENV="$1"
fi

shift
. "build/scripts/env_build_configurators/"$ICONF_SERVER_ENV".sh" "$@"
