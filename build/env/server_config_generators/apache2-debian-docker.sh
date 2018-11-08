##
##  LibreSignage server config generator for the
##  apache2-debian-docker target environment used for
##  building LibreSignage Docker images. This script
##  loads the build configuration using the ldiconf.sh
##  script.
##

set -e
. build/scripts/conf.sh
. build/scripts/ldiconf.sh

mkdir -p "$CONF_DIR"

# Apache2 configuration.
mkdir -p "$CONF_DIR/apache2"
{
echo "Listen *:80"

echo "User docker"
echo "Group www-data"

echo "DocumentRoot /var/www/html"
echo 'ErrorLog ${APACHE_LOG_DIR}/error.log'
echo 'CustomLog ${APACHE_LOG_DIR}/access.log combined'

echo 'RewriteEngine on'
echo 'RewriteRule ^/$ control [L,R=301]'

echo 'ErrorDocument 403 /errors/403/index.php'
echo 'ErrorDocument 404 /errors/404/index.php'
echo 'ErrorDocument 500 /errors/500/index.php'

echo "<Directory \"/var/www/html\">"
echo '	Options -Indexes'
echo '</Directory>'


# Send a 404 response when trying to access 'data/' or 'common/'.
echo "<DirectoryMatch \"^/var/www/html(/?)data(.+)\">"
echo '  RewriteEngine On'
echo '  RewriteRule .* - [R=404,L]'
echo '</DirectoryMatch>'

echo "<DirectoryMatch \"^/var/www/html(/?)common(.+)\">"
echo '  RewriteEngine On'
echo '  RewriteRule .* - [R=404,L]'
echo '</DirectoryMatch>'

} > "$CONF_DIR/apache2/ls-docker.conf"

# PHP configuration.
mkdir -p "$CONF_DIR/php"
{
echo "[PHP]"
echo "file_uploads = On"
echo "upload_max_filesize = 200M"
echo "max_file_uploads = 20"
echo "post_max_size = 210M"
echo "memory_limit = 300M"
} > "$CONF_DIR/php/ls-docker.ini"
