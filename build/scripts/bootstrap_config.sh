#!/bin/sh

read -p "Server environment (Default: apache2-debian): " ICONF_SERVER_ENV
if [ -z "$ICONF_SERVER_ENV" ]; then
	ICONF_SERVER_ENV="apache2-debian";
fi

. "build/scripts/env_build_configurators/"$ICONF_SERVER_ENV".sh"
