#!/bin/sh

# Compare the current version of composer to the required version
# (passed as an argument)

set -e
. build/scripts/dep_checks/dep_util.sh

CURRENT_COMPOSER_VERSION=$(composer --version|cut -d' ' -f2)
ensure_dependency_installed 'composer'
ensure_dependency_version 'composer' $CURRENT_COMPOSER_VERSION $1
