#!/bin/sh

##
##  LibreSignage build configurator script for the apache2-debian-docker
##  environment for building docker images. This script accepts a comma
##  separated list of features to enable as the first argument. The
##  recognised features are:
##
##  - vidthumbs => Video thumbnail generation using ffmpeg.
##  - imgthumbs => Image thumbnail generation using PHP gd.
##  - debug     => Enable debugging.
##

set -e
. build/scripts/conf.sh

ICONF_F="build/docker_`date +%F_%T`$ICONF_FILE_EXT"
echo "Write config to '$ICONF_F'."

{
echo '#!/bin/sh'
echo "# Generated on `date` by configure.sh."

echo "ICONF_SERVER_ENV='$ICONF_SERVER_ENV'"

echo -n 'ICONF_DEBUG='
if [ -z "`echo "$1" | grep -w 'debug'`" ]; then
	echo '"FALSE"'
else
	echo '"TRUE"'
fi

echo -n 'ICONF_FEATURE_VIDTHUMBS='
if [ -z "`echo "$1" | grep -w 'vidthumbs'`" ]; then
	echo '"FALSE"'
else
	echo '"TRUE"'
fi

echo -n 'ICONF_FEATURE_IMGTHUMBS='
if [ -z "`echo "$1" | grep -w 'imgthumbs'`" ]; then
	echo '"FALSE"'
else
	echo '"TRUE"'
fi
} > $ICONF_F

# Create the last config file symlink in build/link/last.sh.
mkdir -p "build/link"
rm -f "build/link/last.sh"
ln -s "`pwd`/$ICONF_F" "build/link/last.sh"

