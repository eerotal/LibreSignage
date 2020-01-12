#!/bin/sh

# Find production dependencies for a Composer package.
# $1 = The package name to query or --self for root.

set -e
if [ -z "$1" ]; then
	echo "[Error] Empty package name."
	exit 1
fi

case "$1" in --*)
	if [ ! "$1" = "--self" ]; then
		echo "[Error] Invalid option '$1'."
		exit 1
	fi
esac

IFS='' # Preserve newline in $deps.
deps=$(composer show --no-ansi "$1" | sed -n '/^requires$/,/^$/{/\(requires\|^$\)/!p}')
IFS='\n'

# Determine deps by recursively running this script.
echo "$deps" | while read l; do
	case "$l" in php*)
		continue
	esac
	echo "$l" && echo "$l" | xargs "$0"
done
