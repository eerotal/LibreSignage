#!/bin/bash

##
## Create the LibreSignage distribution directory and files.
##

set -e
. build/scripts/build_conf.sh

mkdir -p $DIST_DIR;

# Copy LibreSignage files to dist/.
echo '[INFO] Copy LibreSignage files to "'$DIST_DIR'".';

# Exclude *.swp files created by Nano.
find $SRC_DIR/ -type d -exec sh -c \
	'mkdir -p $2/${0#$1/*}' '{}' $SRC_DIR $DIST_DIR \;

find $SRC_DIR/ -type f ! -name '*.swp' -exec sh -c \
	'cp -Rp $0 $2/${0#$1/*}' '{}' $SRC_DIR $DIST_DIR \;

# Copy the README file too.
echo "[INFO] Copy $LS_README to $DIST_DIR.";
cp -Rp $LS_README $RST_DIR/.;

##
## Set the correct file permissions.
##

echo "[INFO] Set default file permissions.";

# Default file permissions.
chown -R $OWNER:$OWNER $DIST_DIR
find $DIST_DIR -type d -exec chmod 755 "{}" ";";
find $DIST_DIR -type f -exec chmod 644 "{}" ";";

echo "[INFO] Set file permissions for the 'data' directory.";

# Permissions for the 'data' directory.
chown -R $OWNER:www-data "$DIST_DIR/data";
find "$DIST_DIR/data" -type d -exec chmod 775 "{}" ";";
find "$DIST_DIR/data" -type f -exec chmod 664 "{}" ";";

