#!/bin/sh

##
##  Execute the system config generator script for the
##  configured target.
##

set -e

. build/scripts/conf.sh
. build/scripts/ldconf.sh

./build/target/"$CONF_TARGET"/system_config.sh $@
