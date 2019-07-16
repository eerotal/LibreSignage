#!/bin/sh

testsuite="$1"

. tests/config.sh
echo "[Info] Cleanup testsuite: $testsuite"

if [ "$testsuite" = "API" ]; then
	if [ ! -z "$TEST_IMG_PATH" ] && [ -f "$TEST_IMG_PATH" ]; then
		rm "$TEST_IMG_PATH";
		rmdir $(dirname "$TEST_IMG_PATH")
	fi
fi
