#!/bin/sh

##
##  LibreSignage target config generator for the
##  apache2-debian-docker target used for building
##  LibreSignage Docker images.
##

set -e
. build/scripts/conf.sh
. build/scripts/ldconf.sh

mkdir -p "$CONF_DIR"

# Apache2 configuration.
mkdir -p "$CONF_DIR/apache2"
{
echo 'Listen *:80'

echo 'User docker'
echo 'Group www-data'

echo 'DocumentRoot /var/www/html'
echo 'ErrorLog ${APACHE_LOG_DIR}/error.log'
echo 'CustomLog ${APACHE_LOG_DIR}/access.log combined'

echo 'ErrorDocument 403 /errors/403/index.php'
echo 'ErrorDocument 404 /errors/404/index.php'
echo 'ErrorDocument 500 /errors/500/index.php'

echo '<Directory "/var/www/html">'

# Disable directory indexing.
echo 'Options -Indexes'

# Redirect / to /control.
echo 'RewriteEngine On'
echo 'RewriteRule "^$" "/control" [R=301,L]'

# Block access to the paths listed in $BLOCKED_PATHS.
echo "RewriteRule \"^($BLOCKED_PATHS)(/.*/?)*$\" - [R=404]"

echo '</Directory>'

} > "$CONF_DIR/apache2/ls-docker.conf"

# PHP configuration.
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
