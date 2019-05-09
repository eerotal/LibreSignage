#!/bin/sh

# Compare the current version of GNU make to the required version
# (passed as an argument)

set -e
. build/scripts/dep_checks/dep_util.sh

CURRENT_MAKE_VERSION=$(make -v | cut -d " " -f 3 | head -n 1)
ensure_dependency_installed 'make'
ensure_dependency_version 'make' $CURRENT_MAKE_VERSION $1
