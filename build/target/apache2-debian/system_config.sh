#!/bin/sh

#
# System configuration generator for the apache2-debian target.
#

set -e
. build/scripts/conf.sh
. build/scripts/args.sh
. build/scripts/ldconf.sh

#
# Setup and parse arguments.
#

script_help() {
	echo 'Usage: ./build/target/apache2-debian/system_config.sh [OPTION]...'
	echo ''
	echo 'Create system configuration files for LibreSignage.'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) ........... DESCRIPTION'
	echo '  --config=FILE (last generated) ... Use a specific build config.'
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
	esac

	set +e
	shift > /dev/null 2>&1
	if [ ! "$?" = 0 ]; then
		set -e
		break
	fi
	set -e
done

load_build_config "$BUILD_CONFIG"

#
# Generate configuration files.
#

# Configure apache2.
mkdir -p "$CONF_DIR/apache2"
{

echo '<VirtualHost *:80>'
if [ -n "$CONF_ADMIN_EMAIL" ]; then
	echo "	ServerAdmin $CONF_ADMIN_EMAIL";
fi
echo "	ServerName $CONF_NAME"
if [ -n "$CONF_ALIAS" ]; then
	echo "	ServerAlias $CONF_ALIAS"
fi
echo "	DocumentRoot $CONF_INSTALL_DIR/$CONF_NAME/public"
echo "	ErrorLog \${APACHE_LOG_DIR}/$CONF_NAME-error.log"
echo "	CustomLog \${APACHE_LOG_DIR}/$CONF_NAME-access.log combined"

echo '	ErrorDocument 403 /errors/403/index.php'
echo '	ErrorDocument 404 /errors/404/index.php'
echo '	ErrorDocument 500 /errors/500/index.php'

echo "	<Directory \"$CONF_INSTALL_DIR/$CONF_NAME/public\">"
echo '		Options -Indexes'
echo '		RewriteEngine On'
echo '		RewriteRule "^$" "/control" [R=301,L]'
echo "		RewriteRule \"^($BLOCKED_PATHS)(/.*/?)*$\" - [R=404]"
echo '	</Directory>'

# Configure PHP.
echo '	php_admin_flag file_uploads On'
echo '	php_admin_value upload_max_filesize 200M'
echo '	php_admin_value max_file_uploads 20'
echo '	php_admin_value post_max_size 210M'
echo '	php_admin_value memory_limit 300M'

echo '</VirtualHost>'

} > "$CONF_DIR/apache2/$CONF_NAME.conf"

# Configure LibreSignage.
mkdir -p "$CONF_DIR/libresignage/conf"
{

echo "<?php"
echo "return ["
echo "	'ADMIN_NAME' => '$CONF_ADMIN_NAME',"
echo "	'ADMIN_EMAIL' => '$CONF_ADMIN_EMAIL'"
echo "];"

} > "$CONF_DIR/libresignage/conf/01-admin-info.php"
