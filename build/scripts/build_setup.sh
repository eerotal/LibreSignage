#!/bin/bash

##
##  Prompt the user for a build system configuration. The
##  config is automatically saved to a file in the build/
##  directory.
##

set -e
. build/scripts/conf.sh

if [ -n "$1" ]; then
	# Load saved config if supplied.
	echo 'Load instance config from "'$1'".';
	. $1;

	# Check the required values are set.
	if [ -z ${INSTC[DOCROOT]} ]; then
		echo '[ERROR] No document root in config.';
		exit 1;
	elif [ -z ${INSTC[NAME]} ]; then
		echo '[ERROR] No server name in config.';
		exit 1;
	fi
else
	# Create a new instance config.
	declare -A INSTC;

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

	read -p 'Enable debugging? (Y/N): ' TMP_IN;
	case $TMP_IN in
		[Yy])
			INSTC[LS_DEBUG]="TRUE";
			;;
		*)
			INSTC[LS_DEBUG]="FALSE";
			;;
	esac

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


