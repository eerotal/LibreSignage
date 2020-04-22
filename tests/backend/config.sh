#!/bin/sh

if [ "$testsuite" = "API" ]; then
	TEST_IMG_PATH="tests/backend/tmp/test.png"
else
	echo "[Info] No config done for testsuite: '$testsuite'."
fi
