#!/bin/sh

testsuite="$1"

. tests/backend/config.sh
echo "[Info] Setup testsuite: $testsuite"

if [ "$testsuite" = "API" ]; then
	mkdir -p $(dirname "$TEST_IMG_PATH")
	sh tests/backend/helpers/gen_test_images.sh "$TEST_IMG_PATH"
fi
