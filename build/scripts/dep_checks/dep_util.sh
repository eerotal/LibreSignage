#!/bin/sh

#
# Utilities for checking LibreSignage dependency versions etc.
#

ensure_dependency_installed() {
	# Check that the dependency $1 exists in $PATH.
	if ! [ -x "$(command -v $1)" ]; then
		echo -n "[Error] Required dependency '$1' is not installed. "
		echo    "Please install it and try again."
		exit 1
	fi
}

ensure_dependency_version() {
	# Compare the semver version numbers from $2 and $3 and error
	# out if $2 is older than $3. $1 is the dependency name.
	if [ "$(echo "$2 $3" | tr " " "\n" | sort -rV | head -n 1)" != "$2" ]; then
		echo -n "[Error] Version $3 required for '$1' but version $2 is "
		echo    "installed."
		exit 1
	fi
}
