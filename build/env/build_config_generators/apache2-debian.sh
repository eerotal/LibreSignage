#!/bin/sh

##
## LibreSignage build configurator script for the apache2-debian
## target environment. This script is interactive.
##

set -e
. build/scripts/conf.sh

read -p 'Install directory (DEF: /var/www): ' CONF_INSTALL_DIR;
if [ -z "$CONF_INSTALL_DIR" ]; then CONF_INSTALL_DIR='/var/www'; fi

read -p 'Server name (domain): ' CONF_NAME
if [ -z "$CONF_NAME" ]; then
	echo '[ERROR] Empty server name.'
	exit 1;
fi

read -p 'Server name aliases (space separated): ' CONF_ALIAS
read -p 'Admin name: ' CONF_ADMIN_NAME
read -p 'Admin email: ' CONF_ADMIN_EMAIL

read -p 'Enable debugging? (y/N): ' TMP_IN
case "$TMP_IN" in
	[Yy])
		CONF_DEBUG="TRUE"
		;;
	*)
		CONF_DEBUG="FALSE"
		;;
esac

##
## Write the build config to file.
##

CONF_FILE="build/$CONF_NAME.conf"
echo "Write config to '$CONF_FILE'."

{

echo '#!/bin/sh'
echo "# Generated on `date` by configure.sh."

echo "CONF_TARGET_ENV=\"apache2-debian\""
echo "CONF_INSTALL_DIR=\"$CONF_INSTALL_DIR\""
echo "CONF_NAME=\"$CONF_NAME\""
echo "CONF_ALIAS=\"$CONF_ALIAS\""
echo "CONF_ADMIN_NAME=\"$CONF_ADMIN_NAME\""
echo "CONF_ADMIN_EMAIL=\"$CONF_ADMIN_EMAIL\""
echo "CONF_DEBUG=\"$CONF_DEBUG\""

} > $CONF_FILE

./build/scripts/mkconfiglink.sh "$CONF_FILE"
