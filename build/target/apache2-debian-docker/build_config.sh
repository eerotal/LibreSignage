#!/bin/sh

set -e
. build/scripts/conf.sh
. build/scripts/args.sh
. build/target/apache2-debian-docker/build_config_defaults.sh

script_help() {
	echo 'Usage:'
	echo '  make configure TARGET=apache2-debian-docker PASS="[OPTION]..."'
	echo '  ./build/target/apache2-debian-docker/build_config.sh [OPTION]...'
	echo ''
	echo 'Create a build configuration for apache2-debian-docker.'
	echo ''
	echo 'Options:'
	echo ''
	echo '  OPTION (DEFAULT VALUE) .......... DESCRIPTION'
	echo '  --feature-imgthumbs (enabled) ... Enable image thumbnail generation.'
	echo '  --feature-vidthumbs (disabled) .. Enable video thumbnail generation.'
	echo '  --debug (disabled)............... Enable debugging.'
	echo '  --help .......................... Display this message and exit.'
}

while [ $# -gt 0 ]; do
	case "$1" in
		--feature-imgthumbs)
			CONF_FEATURE_IMGTHUMBS='TRUE'
			;;
		--feature-vidthumbs)
			CONF_FEATURE_IMGTHUMBS='TRUE'
			;;
		--debug)
			CONF_DEBUG='TRUE'
			;;
		--help)
			script_help
			exit 1
			;;
		*)
			echo "[Error] Unknown option '$1'." > /dev/stderr
			echo ''
			script_help
			exit 1
			;;
	esac

	set +e
	shift > /dev/null 2>&1
	if [ ! "$?" = 0 ]; then break; fi
	set -e
done

#
# Write to the build config file.
#

CONF_FILE="build/docker_`date +%F_%T`.conf"
echo "[Info] Write config to '$CONF_FILE'."

{
echo '#!/bin/sh'
echo "# Generated on `date` by 'make configure'."

echo "CONF_TARGET='apache2-debian-docker'"
echo "CONF_APPROOT='$CONF_APPROOT'"
echo "CONF_ADMIN_NAME='$CONF_ADMIN_NAME'"
echo "CONF_ADMIN_EMAIL='$CONF_ADMIN_EMAIL'"
echo "CONF_FEATURE_IMGTHUMBS='$CONF_FEATURE_IMGTHUMBS'"
echo "CONF_FEATURE_VIDTHUMBS='$CONF_FEATURE_VIDTHUMBS'"
echo "CONF_DEBUG='$CONF_DEBUG'"
} > $CONF_FILE

./build/scripts/mkconfiglink.sh "$CONF_FILE"
