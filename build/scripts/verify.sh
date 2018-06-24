#!/bin/sh

#
#  Build verification code for LibreSignage. Implemented
#  verification flags:
#
#    BUILD_VERIFY_NOCONFIG => Disable warning if config.php is not included.
#

if [ ! "$(ps -o comm= $PPID)" = "make" ]; then
	echo "[Error] LibreSignage build scripts must be run with make!"
	exit 1;
fi

set -e
. build/scripts/conf.sh

for f in `find $DIST_DIR -type f -name '*.php'`; do
	# Check build flags.
	if [ -z "`grep /common/php/config.php $f`" ]; then
		if [ -z "`grep !!BUILD_VERIFY_NOCONFIG!! $f`" ]; then
			echo "Warning: config.php not included in $f.";
			exit 1;
		fi
	elif [ -n "`grep !!BUILD_VERIFY_NOCONFIG!! $f`" ]; then
		echo "Warning: BUILD_VERIFY_NOCONFIG in $f even though" \
			"config.php is included.";
		exit 1;
	fi

	# Check syntax.
	php -l $f;
done

# Run the API unit testing system.
./utests/api/main.py
