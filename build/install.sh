#!/bin/bash

# A script for building LibreSignage and installing an
# apache2 virtual host for hosting a LibreSignage instance.

set -e
. build/build_conf.sh

# Load saved configuration if it is supplied;
if [ -n "$1" ]; then
	echo 'Load install config from "'$1'".';
	. $1;
fi

# Check that the APACHE_SITES directory exists.
if [ ! -d "$APACHE_SITES" ]; then
	echo "[ERROR] The apache2 sites-available directory doesn't exist.";
	echo "[ERROR] Are you sure you have installed Apache2?";
	exit 1;
fi

# Get config information.
if [ -z "$DOCUMENT_ROOT" ]; then
	read -p 'Document root (DEF: /var/www): ' DOCUMENT_ROOT;
	if [ -z "$DOCUMENT_ROOT" ]; then
		DOCUMENT_ROOT='/var/www';
	fi
fi

if [ -z "$SERVER_NAME" ]; then
	read -p 'Domain name of the server: ' SERVER_NAME;
	if [ -z "$SERVER_NAME" ]; then
		echo '[ERROR] Empty domain. Aborting!';
		exit 1;
	fi
fi

if [ -z "$SERVER_ALIAS" ]; then
	read -p 'Server name aliases (space separated): ' SERVER_ALIAS;
fi

if [ -z "$SERVER_ADMIN_EMAIL" ]; then
	read -p 'Admin email (DEF: admin@example.com): ' SERVER_ADMIN_EMAIL;
	if [ -z "$SERVER_ADMIN_EMAIL" ]; then
		SERVER_ADMIN_EMAIL='admin@example.com';
	fi
fi

echo 'Write config to "'$INSTALL_CONFIG'".';
echo '#!/bin/bash' > $INSTALL_CONFIG;
echo 'DOCUMENT_ROOT="'$DOCUMENT_ROOT'";' >> $INSTALL_CONFIG;
echo 'SERVER_NAME="'$SERVER_NAME'";' >> $INSTALL_CONFIG;
echo 'SERVER_ALIAS="'$SERVER_ALIAS'";' >> $INSTALL_CONFIG;
echo 'SERVER_ADMIN_EMAIL="'$SERVER_ADMIN_EMAIL'";' >> $INSTALL_CONFIG;

VHOST_DIR=`echo $DOCUMENT_ROOT'/'$SERVER_NAME | sed "s/\/\+/\//g"`;
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
if [ -n $SUDO_USER ]; then
	echo 'Set virtual host permissions to "'$SUDO_USER':'$SUDO_USER'"';
	chown -R $SUDO_USER:$SUDO_USER $VHOST_DIR
else
	echo 'Set virtual host permissions to "'$USER':'$USER'"';
	chown -R $USER:$USER $VHOST_DIR
fi

echo 'Install LibreSignage to '$VHOST_DIR;
echo 'Copy files.';
cp -Rp $DIST_DIR/* $VHOST_DIR'/.';
echo 'Done!';

echo 'Create the Virtual Host config. ('$APACHE_SITES'/'$SERVER_NAME'.conf)';
if [ -f $APACHE_SITES'/'$SERVER_NAME'.conf' ]; then
	read -p 'Virtual host config exists. Replace? (Y\N): ' repl_vhost_conf
	case $repl_vhost_conf in
		[Yy]* )
			;;
		*)
			echo '[ERROR] Aborting install!';
			exit 1;;
	esac
fi

. 'build/vhost_template.sh' > $APACHE_SITES'/'$SERVER_NAME'.conf';
echo 'LibreSignage installed!';

read -p 'Enable the created virtual host and restart apache2? (Y\N): ' ENABLE_VHOST;
case $ENABLE_VHOST in
	[Yy]* )
		echo 'Enabling site "'$SERVER_NAME'.conf"...';
		a2ensite $SERVER_NAME'.conf';
		echo 'Restarting apache2...';
		systemctl reload apache2
		echo 'Done!';
		;;
	*)
		;;
esac
