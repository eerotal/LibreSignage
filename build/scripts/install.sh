#!/bin/sh

##
## A script for building LibreSignage and installing an
## apache2 virtual host for hosting a LibreSignage instance.
##

set -e
. build/scripts/configure.sh

# Check that the APACHE_SITES directory exists.
if [ ! -d "$APACHE_SITES" ]; then
	echo "[ERROR] Apache2 sites-available directory doesn't exist.";
	echo "[ERROR] Are you sure you have installed Apache 2?";
	exit 1;
fi

VHOST_DIR=`echo "$ICONF_DOCROOT/$ICONF_NAME" | sed "s/\/\+/\//g"`;
echo "[INFO] Virtual host dir: '$VHOST_DIR'";

# Check whether VHOST_DIR already has files.
if [ -n "`ls -A $VHOST_DIR`" ]; then
	echo '[INFO] Virtual host directory is not empty.'
	read -p 'Remove existing files and continue? (Y\N): ' read_val
	case "$read_val" in
		[Yy]* )
			echo '[INFO] Remove existing files.';
			rm -rf $VHOST_DIR;
			;;
		* )
			echo '[ERROR] Aborting install!';
			exit 1;
			;;
	esac
fi

##
##  Copy LibreSignage files.
##

mkdir -p $VHOST_DIR;

echo "[INFO] Install LibreSignage to '$VHOST_DIR'";
echo '[INFO] Copy files.';
cp -Rp $DIST_DIR/* $VHOST_DIR/.;
echo '[INFO] Done!';

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
##  Create config and setup Apache.
##

echo "[INFO] Create VHost config in '$APACHE_SITES/$ICONF_NAME.conf'";
if [ -f "$APACHE_SITES/$ICONF_NAME.conf" ]; then
	read -p 'Replace existing VHost config? (Y\N): ' repl_vhost_conf
	case "$repl_vhost_conf" in
		[Yy]* )
			;;
		*)
			echo '[ERROR] Aborting install!';
			exit 1;;
	esac
fi

. 'build/scripts/vhost_template.sh' > $APACHE_SITES/$ICONF_NAME.conf;
echo '[INFO] LibreSignage installed!';

echo '[INFO] Enable apache2 mod_rewrite...';
a2enmod rewrite;

read -p 'Enable the created VHost and restart apache2? (Y\N): ' EN_VHOST;
case "$EN_VHOST" in
	[Yy]* )
		echo "[INFO] Enabling site '$ICONF_NAME.conf'...";
		a2ensite "$ICONF_NAME.conf";
		echo '[INFO] Restarting apache2...';
		systemctl stop apache2
		systemctl start apache2
		echo '[INFO] Done!';
		;;
	*)
		;;
esac
