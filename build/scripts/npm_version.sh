#! /bin/sh
## Compare NPM version to required (passed as argument)
CURRENT_NPM_VERSION=$(npm -v)
version_check () { test "$(echo "$@" | tr " " "\n" | sort -rV | head -n 1 )" \
	!= "$1"; }
if version_check $CURRENT_NPM_VERSION $1; then
	echo "[ERROR] NPM version $1 unsupported. Please update."
	exit 1
else
	exit 0
fi
