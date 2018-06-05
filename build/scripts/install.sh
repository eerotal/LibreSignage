#!/bin/bash

# A script for building LibreSignage and installing an
# apache2 virtual host for hosting a LibreSignage instance.

INSTC_LOADED=0;

set -e
. build/scripts/build_conf.sh

# Load saved configuration if it is supplied;
if [ -n "$1" ]; then
	echo 'Load install config from "'$1'".';
	. $1;

	# Check the required values are set.
	if [ -z ${INSTC[DOCROOT]} ]; then
		echo '[ERROR] No document root in config.';
		exit 1;
	elif [ -z ${INSTC[NAME]} ]; then
		echo '[ERROR] No server name in config.';
		exit 1;
	fi
	INSTC_LOADED=1;
else
	declare -A INSTC;
fi

# Check that the APACHE_SITES directory exists.
if [ ! -d "$APACHE_SITES" ]; then
	echo "[ERROR] Apache2 sites-available directory doesn't exist.";
	echo "[ERROR] Are you sure you have installed Apache 2?";
	exit 1;
fi

if [ "$INSTC_LOADED" -ne "1" ]; then
	# Get config information.
	read -p 'Document root (DEF: /var/www): ' INSTC[DOCROOT];
	if [ -z "${INSTC[DOCROOT]}" ]; then
		INSTC[DOCROOT]='/var/www';
	fi

	read -p 'Server name (domain): ' INSTC[NAME];
	if [ -z "${INSTC[NAME]}" ]; then
		echo '[ERROR] Empty server name. Aborting!';
		exit 1;
	fi

	read -p 'Server name aliases (space separated): ' INSTC[ALIAS];
	read -p 'Admin name: ' INSTC[ADMIN_NAME];
	read -p 'Admin email: ' INSTC[ADMIN_EMAIL];

	# Write the install config to file.

	INSTC_FILE_NAME='build/'${INSTC[NAME]}$INSTC_FILE_EXT;
	echo 'Write config to "'$INSTC_FILE_NAME'".';

	echo '#!/bin/bash' > $INSTC_FILE_NAME;
	echo "# Generated on `date` by install.sh." >> $INSTC_FILE_NAME;
	echo 'declare -A INSTC=( \' >> $INSTC_FILE_NAME;
	for i in ${!INSTC[@]}; do
		echo "	[$i]=\"${INSTC[$i]}\" \\" >> $INSTC_FILE_NAME;
	done
	echo ');' >> $INSTC_FILE_NAME;
fi

VHOST_DIR=`echo ${INSTC[DOCROOT]}'/'${INSTC[NAME]} | sed "s/\/\+/\//g"`;
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

echo "Create instance config ($VHOST_DIR/$LS_INSTANCE_CONF)...";
sed -i "s/<<ADMIN_NAME>>/${INSTC[ADMIN_NAME]}/g" \
		$VHOST_DIR'/'$LS_INSTANCE_CONF;
sed -i "s/<<ADMIN_EMAIL>>/${INSTC[ADMIN_EMAIL]}/g" \
		$VHOST_DIR'/'$LS_INSTANCE_CONF;

echo 'Create VHost config. ('$APACHE_SITES'/'${INSTC[NAME]}'.conf)';
if [ -f $APACHE_SITES'/'${INSTCONF[NAME]}'.conf' ]; then
	read -p 'Virtual host config exists. Replace? (Y\N): ' repl_vhost_conf
	case $repl_vhost_conf in
		[Yy]* )
			;;
		*)
			echo '[ERROR] Aborting install!';
			exit 1;;
	esac
fi

. 'build/scripts/vhost_template.sh' > $APACHE_SITES'/'${INSTC[NAME]}'.conf';
echo 'LibreSignage installed!';

echo 'Enable apache2 mod_rewrite...';
a2enmod rewrite;

read -p 'Enable the created VHost and restart apache2? (Y\N): ' EN_VHOST;
case $EN_VHOST in
	[Yy]* )
		echo 'Enabling site "'${INSTC[NAME]}'.conf"...';
		a2ensite ${INSTC[NAME]}'.conf';
		echo 'Restarting apache2...';
		systemctl stop apache2
		systemctl start apache2
		echo 'Done!';
		;;
	*)
		;;
esac
