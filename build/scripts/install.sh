#!/bin/sh

set -e
. build/scripts/conf.sh
. build/scripts/ldiconf.sh

./build/env/install_handlers/"$CONF_TARGET_ENV".sh
