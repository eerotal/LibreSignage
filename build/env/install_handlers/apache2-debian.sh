#!/bin/sh

##
## A LibreSignage environment specific install handler for
## apache2 on Debian.
##

set -e
. build/scripts/conf.sh
. build/scripts/ldiconf.sh

FLAG_PRESERVE_DATA=0;

# Environment specific path constants.
SITES_DIR='/etc/apache2/sites-available';
VHOST_DIR=`echo "$ICONF_DOCROOT/$ICONF_NAME" | sed "s/\/\+/\//g"`;

# Check that the SITES_DIR directory exists.
if [ ! -d "$SITES_DIR" ]; then
	echo "[ERROR] '$SITES_DIR' doesn't exist. Is apache2 installed?"
	exit 1
fi

# Check whether VHOST_DIR already has files.
if [ -d "$VHOST_DIR" ]; then
	echo "[INFO] Virtual host directory '$VHOST_DIR' already exists."
	read -p 'Remove (y), preserve data (p) or abort (N): ' read_val;
	case "$read_val" in
		[Yy]* )
			echo '[INFO] Remove all existing files.';
			rm -rf $VHOST_DIR;
			;;
		[Pp]* )
			FLAG_PRESERVE_DATA=1;
			mkdir -p /tmp/libresignage;

			echo "[INFO] Copy instance data to '/tmp/libresignage'.";
			cp -Rp $VHOST_DIR/data/* /tmp/libresignage/.;

			echo '[INFO] Remove existing files.';
			rm -rf $VHOST_DIR;
			;;
		* )
			echo '[INFO] Aborting install.';
			exit 1;
	esac
fi

##
##  Copy LibreSignage files.
##

mkdir -p $VHOST_DIR;

echo "[INFO] Install LibreSignage to '$VHOST_DIR'";
cp -Rp $DIST_DIR/* $VHOST_DIR/.;

if [ "$FLAG_PRESERVE_DATA" = "1" ]; then
	echo '[INFO] Remove new instance data.';
	rm -rf $VHOST_DIR/data/*;

	echo '[INFO] Restore old instance data.';
	cp -Rp /tmp/libresignage/* $VHOST_DIR/data/.
	rm -rf /tmp/libresignage;
fi

##
## Set the correct file permissions.
##

# Default file permissions.
echo "[INFO] Set default file permissions.";
chown -R $OWNER:$OWNER $VHOST_DIR
find $VHOST_DIR -type d -exec chmod 755 "{}" ";";
find $VHOST_DIR -type f -exec chmod 644 "{}" ";";

# Permissions for the 'data' directory.
echo "[INFO] Set file permissions for the 'data' directory.";
chown -R $OWNER:www-data "$VHOST_DIR/data";
find "$VHOST_DIR/data" -type d -exec chmod 775 "{}" ";";
find "$VHOST_DIR/data" -type f -exec chmod 664 "{}" ";";

##
## Final apache2 setup.
##

if [ -f "$SITES_DIR/$ICONF_NAME.conf" ]; then
	echo "[INFO] Apache2 server config already exists."
	read -p "Replace? (y/N) " tmp
	case "$tmp" in
		[Yy]* )
			;;
		*)
			exit 0;
			;;
	esac
fi
cp "$CONF_DIR/sites-available/$ICONF_NAME.conf"	"$SITES_DIR/."

echo "[INFO] Enable virtual host."
a2ensite --quiet "$ICONF_NAME.conf"
echo "[INFO] Enable mod_rewrite."
a2enmod	--quiet	rewrite

read -p	"Restart apache2? (y/N) " tmp
case "$tmp" in  
	[Yy]* )
		echo "[INFO] Restart apache2."
		apache2ctl restart
		;;
	*)
		;;
esac
