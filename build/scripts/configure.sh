#!/bin/sh

##
##  Prompt the user for a build system configuration. The
##  config is automatically saved to a file in 'build/'.
##

if [ ! "$(ps -o comm= $PPID)" = "make" ]; then
	echo "[Error] LibreSignage build scripts must be run with make!"
	exit 1;
fi

set -e
. build/scripts/conf.sh

check_config() {
	# Check the required values are set.
	if [ -z "$ICONF_DOCROOT" ]; then
		echo '[ERROR] No document root in config.';
		exit 1;
	elif [ -z "$ICONF_NAME" ]; then
		echo '[ERROR] No server name in config.';
		exit 1;
	fi
}

if [ -n "$1" ]; then
	# Load saved config if supplied.
	echo "Load instance config from '$1'.";
	. "$1";
	check_config;
else
	FLAG_LAST_LOADED=0;
	if [ -f 'build/link/last.sh' ]; then
		read -p "Load last config? (Y/N): " LOAD_LAST
		case "$LOAD_LAST" in
			[Yy])
				echo '[INFO] Load config.';
				. 'build/link/last.sh';
				check_config;
				FLAG_LAST_LOADED=1;
				;;
			*)
				FLAG_LAST_LOADED=0;
				;;
		esac

		if [ "$FLAG_LAST_LOADED" = "1" ]; then
			if [ "$(basename "$0")" = "configure.sh" ]; then
				exit 0;
			else
				return 0;
			fi
		fi
	fi

	# Get config information.
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

	read -p 'Enable debugging? (Y/N): ' TMP_IN;
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
	echo "ICONF_ADMIN_NAME=\"$ICONF_ADMIN_NAME\";"    >> $ICONF_F;
	echo "ICONF_ADMIN_EMAIL=\"$ICONF_ADMIN_EMAIL\";"  >> $ICONF_F;
	echo "ICONF_DEBUG=\"$ICONF_DEBUG\";"              >> $ICONF_F;

	# Create the last config file symlink in build/link/last.sh.
	mkdir -p "build/link";
	rm -f "build/link/last.sh"
	ln -s "`pwd`/$ICONF_F" "build/link/last.sh";
fi


