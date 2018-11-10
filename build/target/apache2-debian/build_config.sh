#!/bin/sh

##
##  LibreSignage build config generator for the apache2-debian
##  target. This script is interactive.
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

read -p 'Enable image thumbnail generation? (y/N): ' CONF_FEATURE_IMGTHUMBS
case "$CONF_FEATURE_IMGTHUMBS" in
	[Yy])
		CONF_FEATURE_IMGTHUMBS="TRUE"
		;;
	*)
		CONF_FEATURE_IMGTHUMBS="FALSE"
		;;
esac

read -p 'Enable video thumbnail generation? (y/N): ' CONF_FEATURE_VIDTHUMBS
case "$CONF_FEATURE_VIDTHUMBS" in
	[Yy])
		CONF_FEATURE_VIDTHUMBS="TRUE"
		;;
	*)
		CONF_FEATURE_VIDTHUMBS="FALSE"
		;;
esac

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

echo "CONF_TARGET=\"apache2-debian\""
echo "CONF_INSTALL_DIR=\"$CONF_INSTALL_DIR\""
echo "CONF_NAME=\"$CONF_NAME\""
echo "CONF_ALIAS=\"$CONF_ALIAS\""
echo "CONF_ADMIN_NAME=\"$CONF_ADMIN_NAME\""
echo "CONF_ADMIN_EMAIL=\"$CONF_ADMIN_EMAIL\""
echo "CONF_FEATURE_IMGTHUMBS=\"$CONF_FEATURE_IMGTHUMBS\""
echo "CONF_FEATURE_VIDTHUMBS=\"$CONF_FEATURE_VIDTHUMBS\""
echo "CONF_DEBUG=\"$CONF_DEBUG\""

} > $CONF_FILE

./build/scripts/mkconfiglink.sh "$CONF_FILE"
