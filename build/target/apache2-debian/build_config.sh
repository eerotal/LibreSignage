#!/bin/sh

#
# LibreSignage build configuration script for the apache2-debian target.
#
# You can also use build/scripts/apache2-debian/build_config_interactive.sh
# for an interactve wrapper for this script.
#

set -e
. build/scripts/conf.sh
. build/scripts/args.sh
. build/target/apache2-debian/build_config_defaults.sh

script_help() {
	echo 'Usage: ./build/target/apache2-debian/build_configure.sh [OPTION]...'
	echo ''
	echo 'Create a LibreSignage build configuration file.'
	echo ''
	echo 'Options:'
	echo ''
	echo '  OPTION (DEFAULT VALUE) .................... DESCRIPTION';
	echo '  --install-dir=PATH (/var/www) ............. The installation directory to use.'
	echo '  --server-name=NAME (localhost) ............ The domain name to use for the server.'
	echo '  --server-aliases=ALIASES .................. A space separated list of domain aliases.'
	echo '  --admin-name=NAME (Example Admin).......... The name of the admin.'
	echo '  --admin-email=EMAIL (admin@example.com) ... The email address of the admin.'
	echo '  --feature-imgthumbs (enabled) ............. Enable image thumbnail generation.'
	echo '  --feature-vidthumbs (disabled) ............ Enable video thumbnail generation.'
	echo '  --debug (disabled)......................... Enable debugging.'
	echo '  --help .................................... Display this message and exit.'
}

while [ $# -gt 0 ]; do
	case "$1" in
		--install-dir=*)
			CONF_INSTALL_DIR=$(get_arg_value "$1")
			;;
		--server-name=*)
			CONF_NAME=$(get_arg_value "$1")
			;;
		--server-aliases=*)
			CONF_ALIAS=$(get_arg_value "$1")
			;;
		--admin-name=*)
			CONF_ADMIN_NAME=$(get_arg_value "$1")
			;;
		--admin-email=*)
			CONF_ADMIN_EMAIL=$(get_arg_value "$1")
			;;
		--feature-imgthumbs)
			CONF_FEATURE_IMGTHUMBS="TRUE"
			;;
		--feature-vidthumbs)
			CONF_FEATURE_VIDTHUMBS="TRUE"
			;;
		--debug)
			CONF_DEBUG="TRUE"
			;;
		--help)
			script_help
			exit 0
			;;
		*)
			echo "[Error] Unknown option '$1'." > /dev/stderr
			echo ''
			script_help
			exit 1
			;;
	esac

	set +e
	shift > /dev/null 2>&1 && if [ ! "$?" = 0 ]; then break; fi
	set -e
done

#
# Write the build config to file.
#

CONF_FILE="build/$CONF_NAME.conf"
echo "[Info] Write config to '$CONF_FILE'."

{
echo '#!/bin/sh'
echo "# Generated on `date` by 'make configure'."

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
