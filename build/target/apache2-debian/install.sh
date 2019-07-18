#!/bin/sh

#
# An install handler for the apache2-debian target.
#

set -e
. build/scripts/conf.sh
. build/scripts/ldconf.sh

FLAG_PRESERVE_DATA=0;

# Target specific path constants.
SITES_DIR='/etc/apache2/sites-available';
VHOST_DIR=`echo "$CONF_INSTALL_DIR/$CONF_NAME" | sed "s/\/\+/\//g"`;

# Check that the SITES_DIR directory exists.
if [ ! -d "$SITES_DIR" ]; then
	echo "[Error] '$SITES_DIR' doesn't exist. Is apache2 installed?"
	exit 1
fi

# Check whether VHOST_DIR already has files.
if [ -d "$VHOST_DIR" ]; then
	echo "[Info] Virtual host directory '$VHOST_DIR' already exists."
	read -p 'Remove (y), preserve data (p) or abort (N): ' read_val;
	case "$read_val" in
		[Yy]* )
			echo '[Info] Remove all existing files.';
			rm -rf $VHOST_DIR;
			;;
		[Pp]* )
			FLAG_PRESERVE_DATA=1;
			mkdir -p /tmp/libresignage;

			echo "[Info] Copy instance data to '/tmp/libresignage'.";
			cp -Rp $VHOST_DIR/data/* /tmp/libresignage/.;

			echo '[Info] Remove existing files.';
			rm -rf $VHOST_DIR;
			;;
		* )
			echo '[Info] Aborting install.';
			exit 1;
	esac
fi

##
##  Copy LibreSignage files.
##

mkdir -p $VHOST_DIR;

echo "[Info] Install LibreSignage to '$VHOST_DIR'";
cp -Rp $DIST_DIR/* $VHOST_DIR/.;

if [ "$FLAG_PRESERVE_DATA" = "1" ]; then
	echo '[Info] Remove new instance data.';
	rm -rf $VHOST_DIR/data/*;

	echo '[Info] Restore old instance data.';
	cp -Rp /tmp/libresignage/* $VHOST_DIR/data/.
	rm -rf /tmp/libresignage;
fi

# Copy config files.
cp server/libresignage/conf/* $VHOST_DIR/config/conf/.

# Set default file permissions.
echo "[Info] Set default file permissions.";
chown -R $OWNER:$OWNER "$VHOST_DIR"
chown -R $OWNER:www-data "$VHOST_DIR/data";
find "$VHOST_DIR" -type d -print0 | xargs -0 chmod 755
find "$VHOST_DIR/data" -type d -print0 | xargs -0 chmod 775
find "$VHOST_DIR" -type f -print0 | xargs -0 chmod 644
find "$VHOST_DIR/data" -type f -print0 | xargs -0 chmod 664

# Create $LOG_DIR.
echo "[Info] Create log directory '$LOG_DIR'."
mkdir -p "$LOG_DIR"
chown root:www-data "$LOG_DIR"
chmod 775 "$LOG_DIR"

##
## Apache2 setup.
##

if [ -f "$SITES_DIR/$CONF_NAME.conf" ]; then
	echo "[Info] Apache2 server config already exists."
	read -p "Replace? (y/N) " tmp
	case "$tmp" in
		[Yy]* )
			;;
		*)
			exit 0;
			;;
	esac
fi
cp "$CONF_DIR/apache2/$CONF_NAME.conf" "$SITES_DIR/."

echo "[Info] Enable virtual host."
a2ensite --quiet "$CONF_NAME.conf"
echo "[Info] Enable mod_rewrite."
a2enmod	--quiet	rewrite

read -p	"Restart apache2? (y/N) " tmp
case "$tmp" in  
	[Yy]* )
		echo "[Info] Restart apache2."
		apache2ctl restart
		;;
	*)
		;;
esac
