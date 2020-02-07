#!/bin/sh

#
# System configuration generator for the apache2-debian-docker target.
#

set -e
. build/scripts/conf.sh
. build/scripts/ldconf.sh
. build/scripts/args.sh

#
# Setup and parse arguments.
#

script_help() {
	echo 'Usage: ./build/target/apache2-debian-docker/system_config.sh [OPTION]...'
	echo ''
	echo 'Generate LibreSignage system configuration files.'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) .... DESCRIPTION'
	echo '  --config=FILE (last generated) ... Use a specific configuration file.'
	echo '  --help ........................... Print this message and exit.'
}

BUILD_CONFIG=''

while [ $# -gt 0 ]; do
	case "$1" in
		--config=*)
			BUILD_CONFIG="$(get_arg_value "$1")"
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
	shift > /dev/null 2>&1
	if [ ! "$?" = "0" ]; then
		set -e
		break
	fi
	set -e
done

load_build_config "$BUILD_CONFIG"

#
# Generate configuration files.
#

mkdir -p "$CONF_DIR"

# Apache2 configuration.
mkdir -p "$CONF_DIR/apache2"
{
echo 'Listen *:80'

echo 'User docker'
echo 'Group www-data'

echo "DocumentRoot \"$CONF_APPROOT/public\""
echo 'ErrorLog ${APACHE_LOG_DIR}/error.log'
echo 'CustomLog ${APACHE_LOG_DIR}/access.log combined'

echo 'ErrorDocument 403 /errors/403/index.php'
echo 'ErrorDocument 404 /errors/404/index.php'
echo 'ErrorDocument 500 /errors/500/index.php'

echo "<Directory \"$CONF_APPROOT/public\">"

# Disable directory indexing.
echo 'Options -Indexes'

# Redirect / to /control.
echo 'RewriteEngine On'
echo 'RewriteRule "^$" "/control" [R=301,L]'

# Block access to the paths listed in $BLOCKED_PATHS.
echo "RewriteRule \"^($BLOCKED_PATHS)(/.*/?)*$\" - [R=404]"

echo '</Directory>'

} > "$CONF_DIR/apache2/ls-docker.conf"

# Configure PHP.
mkdir -p "$CONF_DIR/php"
{

echo '[PHP]'
echo 'file_uploads = On'
echo 'upload_max_filesize = 200M'
echo 'max_file_uploads = 20'
echo 'post_max_size = 210M'
echo 'memory_limit = 300M'

if [ "$CONF_FEATURE_IMGTHUMBS" = "TRUE" ]; then
	echo 'extension=gd.so'
fi

} > "$CONF_DIR/php/ls-docker.ini"

# Configure LibreSignage.
mkdir -p "$CONF_DIR/libresignage/conf"
{

echo "<?php"
echo "return ["
echo "	'LS_VER' => '$LS_VER',"
echo "	'API_VER' => '$API_VER',"
echo "	'LIBRESIGNAGE_DEBUG' => $CONF_DEBUG,"
echo "	'ENABLE_FFMPEG_THUMBS' => $CONF_FEATURE_VIDTHUMBS,"
echo "	'ENABLE_GD_THUMBS' => $CONF_FEATURE_IMGTHUMBS"
echo "];"

} > "$CONF_DIR/libresignage/conf/01-server-config.php"
