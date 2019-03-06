#!/bin/sh

##
##  Prompt the user for a build system configuration. The
##  config is automatically saved to a file in 'build/'.
##

set -e
. build/scripts/conf.sh

# Check if NPM is proper version
MINIMUM_NPM_VERSION="6.0.0"
CURRENT_NPM_VERSION=$(npm -v)


version_check () { test "$(echo "$@" | tr " " "\n" | sort -rV | head -n 1)" != "$1"; }
if version_check $CURRENT_NPM_VERSION $MINIMUM_NPM_VERSION; then
        echo "[ERROR] NPM version '$CURRENT_NPM_VERSION' is below requirement of '$MINIMUM_NPM_VERSION'"
        echo "[ERROR] Please install an updated version and run 'npm install' again"
	echo "[ERROR] See the README for details"
        exit 1;
fi



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

