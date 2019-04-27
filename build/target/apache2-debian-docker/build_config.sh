#!/bin/sh

##
##  LibreSignage build config generator script for the
##  apache2-debian-docker target used for building
##  Docker images. 
##  
##  You can pass the following CLI options when running
##  this script:
##
##  --features [FEATURES]
##      * A comma separated list of features to enable.
##        Recognised features are:
##          > imgthumbs: Enable image thumbnails using PHP gd.
##          > vidthumbs: Enable video thumbnails using ffmpeg.
##          > debug:     Enable debugging.
##

set -e
. build/scripts/conf.sh

features=''

is_feature_enabled() {
	if [ -z "`echo "$1" | grep -w \"$2\"`" ]; then
		return 1
	else
		return 0
	fi
}

##
## Parse CLI options.
##

while [ $# -gt 0 ]; do
	case "$1" in
		'--features')
			if [ ! -z "$2" ]; then
				shift
				features="$1"
				shift
			else
				echo "[Error] Expected a comma separated "\
					"list after '--features'."
				exit 1
			fi
			;;
		*)
			echo "[Error] Unknown option '$1'"
			exit 1
			;;
	esac
done

##
##  Write to the build config file.
##

CONF_FILE="build/docker_`date +%F_%T`.conf"
echo "Write config to '$CONF_FILE'."

{
echo '#!/bin/sh'
echo "# Generated on `date` by"
echo "# the LibreSignage build system."

echo "CONF_TARGET='apache2-debian-docker'"
echo "CONF_DOCROOT='/var/www/html'"
echo "CONF_ADMIN_NAME='admin'"
echo "CONF_ADMIN_EMAIL='admin@example.com'"

echo -n 'CONF_DEBUG='
if is_feature_enabled "$features" 'debug'; then
	echo '"TRUE"'
else
	echo '"FALSE"'
fi

echo -n 'CONF_FEATURE_VIDTHUMBS='
if is_feature_enabled "$features" 'vidthumbs'; then
	echo '"TRUE"'
else
	echo '"FALSE"'
fi

echo -n 'CONF_FEATURE_IMGTHUMBS='
if is_feature_enabled "$features" 'imgthumbs'; then
	echo '"TRUE"'
else
	echo '"FALSE"'
fi
} > $CONF_FILE

./build/scripts/mkconfiglink.sh "$CONF_FILE"
