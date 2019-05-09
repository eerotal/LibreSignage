#!/bin/sh

# Compare the current version of Pandoc to the required version
# (passed as an argument)

set -e
. build/scripts/dep_checks/dep_util.sh

CURRENT_PANDOC_VERSION=$(pandoc -v | head -n 1 | cut -d " " -f 2)
ensure_dependency_installed 'pandoc'
ensure_dependency_version 'pandoc' $CURRENT_PANDOC_VERSION $1
