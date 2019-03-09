#! /bin/sh
## Compare current version of make to required version (passed as argument)

set -e
CURRENT_MAKE_VERSION=$(make -v | cut -d " " -f 3 | head -n 1)
version_check () { test "$(echo "$@" | tr " " "\n" | sort -rV | head -n 1 )" \
	!= "$1"; }
if version_check $CURRENT_MAKE_VERSION $1; then
	echo "[ERROR] Make version $1 required. Please update."
	exit 1
else
	exit 0
fi
