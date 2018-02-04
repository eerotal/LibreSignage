#
#  Build verification code for LibreSignage. Implemented
#  verification flags:
#
#    BUILD_VERIFY_NOCONFIG => Disable warning if config.php is not included.
#

for f in `find . -type f -name '*.php'`; do
	if [ -z "`grep /common/php/config.php $f`" ]; then
		if [ -z "`grep !!BUILD_VERIFY_NOCONFIG!! $f`" ]; then
			echo "Warning: config.php not included in "$f".";
		fi
	elif [ -n "`grep !!BUILD_VERIFY_NOCONFIG!! $f`" ]; then
		echo "Warning: BUILD_VERIFY_NOCONFIG in $f even though" \
			"config.php is included.";
	fi
done
