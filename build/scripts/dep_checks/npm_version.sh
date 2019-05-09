#!/bin/sh

# Compare the current version of NPM to the required version
# (passed as an argument)

set -e
. build/scripts/dep_checks/dep_util.sh

CURRENT_NPM_VERSION=$(npm -v)
ensure_dependency_installed 'npm'
ensure_dependency_version 'npm' $CURRENT_NPM_VERSION $1
