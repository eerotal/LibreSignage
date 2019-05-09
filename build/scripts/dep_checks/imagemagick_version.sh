#!/bin/sh

# Compare the current version of ImageMagick to the required version
# (passed as an argument)

set -e
. build/scripts/dep_checks/dep_util.sh

CURRENT_IMAGEMAGICK_VERSION=$(convert --version | head -n 1 | cut -d " " -f 3)
ensure_dependency_installed 'convert'
ensure_dependency_version 'convert' $CURRENT_IMAGEMAGICK_VERSION $1
