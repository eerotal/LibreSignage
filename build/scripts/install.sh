#!/bin/bash

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
echo "Virtual host dir: "$VHOST_DIR;

# Check whether VHOST_DIR already has files.
if [ -n "`ls -A $VHOST_DIR`" ]; then
	echo 'Virtual host directory is not empty.'
	read -p 'Remove existing files and continue? (Y\N): ' read_val
	case $read_val in
		[Yy]* )
			echo 'Remove existing files.';
			rm -rfv $VHOST_DIR;
			;;
		* )
			echo '[ERROR] Aborting install!';
			exit 1;;
	esac
fi

mkdir -p $VHOST_DIR;
echo 'Set virtual host permissions to "'$OWNER':'$OWNER'"';
chown -R $OWNER:$OWNER $VHOST_DIR

echo 'Install LibreSignage to '$VHOST_DIR;
echo 'Copy files.';
cp -Rp $DIST_DIR/* $VHOST_DIR'/.';
echo 'Done!';

echo "Create VHost config. ($APACHE_SITES/$ICONF_NAME.conf)";
if [ -f "$APACHE_SITES/$ICONF_NAME.conf" ]; then
	read -p 'Replace existing VHost config? (Y\N): ' repl_vhost_conf
	case $repl_vhost_conf in
		[Yy]* )
			;;
		*)
			echo '[ERROR] Aborting install!';
			exit 1;;
	esac
fi

. 'build/scripts/vhost_template.sh' > "$APACHE_SITES/$ICONF_NAME.conf";
echo 'LibreSignage installed!';

echo 'Enable apache2 mod_rewrite...';
a2enmod rewrite;

read -p 'Enable the created VHost and restart apache2? (Y\N): ' EN_VHOST;
case $EN_VHOST in
	[Yy]* )
		echo "Enabling site '$ICONF_NAME.conf'...";
		a2ensite "$ICONF_NAME.conf";
		echo 'Restarting apache2...';
		systemctl stop apache2
		systemctl start apache2
		echo 'Done!';
		;;
	*)
		;;
esac
