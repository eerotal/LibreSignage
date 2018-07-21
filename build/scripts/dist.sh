#!/bin/sh

##
## Create the LibreSignage distribution directory and files.
##

set -e;
. build/scripts/configure.sh

##
## Copy non-JS LibreSignage files to 'dist/'.
##

echo "[INFO] Copy LibreSignage files to '$DIST_DIR'.";

mkdir -p $DIST_DIR;
find $SRC_DIR/ -type d -exec sh -c \
	'mkdir -p $2/${0#$1/*}' '{}' $SRC_DIR $DIST_DIR \;
find $SRC_DIR/ -type f ! -name '*.swp' ! -name '*.js' -exec sh -c \
	 'cp -Rp $0 $2/${0#$1/*}' '{}' $SRC_DIR $DIST_DIR \;

# Copy the README file.
echo "[INFO] Copy $LS_README to $DIST_DIR.";
cp -Rp $LS_README $RST_DIR/.;

##
## Copy node_modules to dist/libs.
##

mkdir -p "$DIST_DIR/libs";
echo "[INFO] Copy modules to '$DIST_DIR/libs'.";
cp -Rp node_modules/* "$DIST_DIR/libs";

##
## Apply build time string constants to the config file.
##

echo '[INFO] Replace build constants in config.php.';
CONF=`cat "$DIST_DIR/common/php/config.php"`
echo "$CONF" | grep -o '!!BCONST_.*!!' | while read -r line; do
	VN=`echo "$line" | cut -c10- | rev | cut -c3- | rev`;
	eval FN="\$ICONF_$VN";

	if [ -z "$FN" ]; then
		echo "[Error] Constant $VN is not set.";
		exit 1;
	else
		echo "[INFO] BCONF_$VN ==> $FN";
		sed -i "s/!!BCONST_$VN!!/$FN/g" \
			"$DIST_DIR/common/php/config.php";
	fi
done
