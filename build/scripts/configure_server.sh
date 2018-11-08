#!/bin/sh

##
##  Execute the target config generator script for the
##  configured environment.
##

set -e

. build/scripts/conf.sh
. build/scripts/ldconf.sh

./build/env/target_config_generators/"$CONF_TARGET_ENV".sh $@
