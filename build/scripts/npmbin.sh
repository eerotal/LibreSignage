#!/bin/sh

##
## Get the global NPM bin path for the non-sudo user.
##

set -e;
. build/scripts/conf.sh;

echo `sudo -u $OWNER npm bin 2>/dev/null`;
