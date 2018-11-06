#!/bin/sh

##
## LibreSignage build configurator script for the apache2-debian
## environment. This script is interactive.
##

set -e
. build/scripts/conf.sh

read -p 'Document root (DEF: /var/www): ' ICONF_DOCROOT;
if [ -z "$ICONF_DOCROOT" ]; then
	ICONF_DOCROOT='/var/www';
fi

read -p 'Server name (domain): ' ICONF_NAME;
if [ -z "$ICONF_NAME" ]; then
	echo '[ERROR] Empty server name. Aborting!';
	exit 1;
fi

read -p 'Server name aliases (space separated): ' ICONF_ALIAS;
read -p 'Admin name: ' ICONF_ADMIN_NAME;
read -p 'Admin email: ' ICONF_ADMIN_EMAIL;

read -p 'Enable debugging? (y/N): ' TMP_IN;
case "$TMP_IN" in
	[Yy])
		ICONF_DEBUG="TRUE";
		;;
	*)
		ICONF_DEBUG="FALSE";
		;;
esac

# Write the install config to file.
ICONF_F="build/$ICONF_NAME$ICONF_FILE_EXT";
echo "Write config to '$ICONF_F'.";

echo '#!/bin/sh'                                  >  $ICONF_F;
echo "# Generated on `date` by configure.sh."     >> $ICONF_F;

echo "ICONF_SERVER_ENV=\"$ICONF_SERVER_ENV\";"    >> $ICONF_F;
echo "ICONF_DOCROOT=\"$ICONF_DOCROOT\";"          >> $ICONF_F;
echo "ICONF_NAME=\"$ICONF_NAME\";"                >> $ICONF_F;
echo "ICONF_ALIAS=\"$ICONF_ALIAS\";"              >> $ICONF_F;
echo "ICONF_ADMIN_NAME=\"$ICONF_ADMIN_NAME\";"    >> $ICONF_F;
echo "ICONF_ADMIN_EMAIL=\"$ICONF_ADMIN_EMAIL\";"  >> $ICONF_F;
echo "ICONF_DEBUG=\"$ICONF_DEBUG\";"              >> $ICONF_F;

# Create the last config file symlink in build/link/last.sh.
mkdir -p "build/link";
rm -f "build/link/last.sh"
ln -s "`pwd`/$ICONF_F" "build/link/last.sh";

