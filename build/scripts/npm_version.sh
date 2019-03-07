
CURRENT_NPM_VERSION=$(npm -v)
version_check () { test "$(echo "$@" | tr " " "\n" | sort -rV | head -n 1 )" != "$1"; }
if version_check $CURRENT_NPM_VERSION $1; then
	echo "0"
	exit 0
else
	echo "1"
	exit 1
fi
