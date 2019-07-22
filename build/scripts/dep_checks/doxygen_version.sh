#!/bin/sh

# Compare the current version of Doxygen to the required version
# (passed as an argument)

set -e
. build/scripts/dep_checks/dep_util.sh

CURRENT_DOXYGEN_VERSION=$(doxygen -v)
ensure_dependency_installed 'doxygen'
ensure_dependency_version 'doxygen' $CURRENT_DOXYGEN_VERSION $1
