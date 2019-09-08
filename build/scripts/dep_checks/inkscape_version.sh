#!/bin/sh

# Compare the current version of Inkscape to the required version
# (passed as an argument)

set -e
. build/scripts/dep_checks/dep_util.sh

CURRENT_INKSCAPE_VERSION=$(inkscape --version 2>/dev/null|cut -d' ' -f2)
ensure_dependency_installed 'inkscape'
ensure_dependency_version 'inkscape' $CURRENT_INKSCAPE_VERSION $1
