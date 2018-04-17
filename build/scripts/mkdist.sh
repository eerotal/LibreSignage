#!/bin/bash

set -e
. build/scripts/build_conf.sh

mkdir -p $DIST_DIR;

# Copy LibreSignage files to dist/.
echo '[INFO] Copy LibreSignage files to "'$DIST_DIR'".';

# Exclude *.swp files created by Nano.
find $SRC_DIR -type d -exec sh -c \
	'mkdir -p $2/${0#$1/*}' '{}' $SRC_DIR $DIST_DIR \;

find $SRC_DIR -type f ! -name '*.swp' -exec sh -c \
	'cp -Rp $0 $2/${0#$1/*}' '{}' $SRC_DIR $DIST_DIR \;

# Copy the README file too.
echo "[INFO] Copy $LS_README to $DIST_DIR.";
cp -Rp $LS_README $RST_DIR/.;

# Compress the client files.
echo "[INFO] Compress client files. ($CLIENT_DIR)"

cd $CLIENT_DIR;

if ! `command -v tar &>/dev/null`; then
	echo "[INFO] 'tar' is not installed."
	echo "[INFO] Install 'tar' if you need the client tarball."
else
	echo "[INFO] Compress tarball:"
	tar -zcvf client.tar.gz client.html;
fi

if ! `command -v zip &>/dev/null`; then
	echo "[INFO] 'zip' is not installed.";
	echo "[INFO] Install 'zip' if you need the client ZIP archive.";
else
	echo "[INFO] Compress zip:";
	zip -r client.zip client.html -x *.tar.gz
fi

cd -;

# Set correct file permissions.
echo "[INFO] Set file permissions (Owner: $DEF_OWNER | Mode: $DEF_MODE)";
chown -R $DEF_OWNER $DIST_DIR;
chmod -R $DEF_MODE $DIST_DIR;

echo "[INFO] Set file permission exceptions.";
for i in "${!owner_data[@]}"; do
	echo "${owner_data[$i]} $DIST_DIR/$i" | xargs -t chown
done

for i in "${!mode_data[@]}"; do
	echo "${mode_data[$i]} $DIST_DIR/$i" | xargs -t chmod
done
