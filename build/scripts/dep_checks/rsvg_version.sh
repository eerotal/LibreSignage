#!/bin/sh

# Compare the current version of Inkscape to the required version
# (passed as an argument)

set -e
. build/scripts/dep_checks/dep_util.sh

CURRENT_RSVG_VERSION=$(inkscape --version 2>/dev/null|cut -d' ' -f3)
ensure_dependency_installed 'rsvg-convert'
ensure_dependency_version 'rsvg-convert' $CURRENT_RSVG_VERSION $1
