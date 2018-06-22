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

WFLAG=0

for f in `find $SRC_DIR -type f -name '*.php'`; do
	if [ -z "`grep /common/php/config.php $f`" ]; then
		if [ -z "`grep !!BUILD_VERIFY_NOCONFIG!! $f`" ]; then
			echo "Warning: config.php not included in $f.";
			WFLAG=1;
		fi
	elif [ -n "`grep !!BUILD_VERIFY_NOCONFIG!! $f`" ]; then
		echo "Warning: BUILD_VERIFY_NOCONFIG in $f even though" \
			"config.php is included.";
		WFLAG=1;
	fi
done

# Ask the user whether to abort the build process.
if [ "$WFLAG" = "1" ]; then
	read -p "Abort the build process? (Y/N) " uinput;
	if [ "$uinput" = "y" ] || [ "$uinput" = "Y" ]; then
		exit 1;
	fi
fi
