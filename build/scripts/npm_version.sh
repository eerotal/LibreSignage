#!/bin/sh

# Compare the current version of NPM to the required version
# (passed as an argument)

set -e
CURRENT_NPM_VERSION=$(npm -v)
version_check() {
	test "$(echo "$@" | tr " " "\n" | sort -rV | head -n 1 )" != "$1"
}

if version_check $CURRENT_NPM_VERSION $1; then
	echo "[Error] NPM version $1 required. Please update."
	exit 1
else
	exit 0
fi
