#!/bin/sh

##
##  Execute the correct install handler for the
##  configured target.
##

set -e
. build/scripts/conf.sh
. build/scripts/ldconf.sh

./build/target/"$CONF_TARGET"/install.sh
