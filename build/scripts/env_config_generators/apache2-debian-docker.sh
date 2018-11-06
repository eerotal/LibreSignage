#
#  LibreSignage environment config generator for the
#  apache2-debian-docker environment that's used for building
#  LibreSignage Docker images. The apache2 configuration
#  in the Docker image doesn't use Virtual Hosts as that makes
#  the configuration code simpler and VHosts have no use in
#  containers.
#

set -e
. build/scripts/conf.sh
. build/scripts/ldiconf.sh

mkdir -p "$CONF_DIR/conf-available";

{
echo "Listen *:80";

echo "User docker";
echo "Group www-data";

echo "DocumentRoot /var/www/html";
echo 'ErrorLog ${APACHE_LOG_DIR}/error.log';
echo 'CustomLog ${APACHE_LOG_DIR}/access.log combined';

echo 'RewriteEngine on';
echo 'RewriteRule ^/$ control [L,R=301]';

echo 'ErrorDocument 403 /errors/403/index.php';
echo 'ErrorDocument 404 /errors/404/index.php';
echo 'ErrorDocument 500 /errors/500/index.php';

echo "<Directory \"/var/www/html\">";
echo '	Options -Indexes';
echo '</Directory>';


# Send a 404 response when trying to access 'data/' or 'common/'.
echo "<DirectoryMatch \"^/var/www/html(/?)data(.+)\">";
echo '  RewriteEngine On';
echo '  RewriteRule .* - [R=404,L]';
echo '</DirectoryMatch>';

echo "<DirectoryMatch \"^/var/www/html(/?)common(.+)\">";
echo '  RewriteEngine On';
echo '  RewriteRule .* - [R=404,L]';
echo '</DirectoryMatch>';

} > "$CONF_DIR/conf-available/ls-docker.conf"
