#!/bin/sh

##
##  Execute the server config generator script for the
##  configured build environment.
##

set -e

. build/scripts/conf.sh
. build/scripts/ldiconf.sh

./build/env/server_config_generators/"$CONF_TARGET_ENV".sh $@
