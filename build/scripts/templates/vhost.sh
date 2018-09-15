# LibreSignage Virtual Host configuration template.
# This file is sourced in install.sh.

echo '<VirtualHost *:80>';
if [ -n "$ICONF_ADMIN_EMAIL" ]; then
	echo "	ServerAdmin $ICONF_ADMIN_EMAIL";
fi
echo "	ServerName $ICONF_NAME";
if [ -n "$ICONF_ALIAS" ]; then
	echo "	ServerAlias $ICONF_ALIAS";
fi
echo "	DocumentRoot $ICONF_DOCROOT/$ICONF_NAME";
echo '	ErrorLog ${APACHE_LOG_DIR}/error.log';
echo '	CustomLog ${APACHE_LOG_DIR}/access.log combined';

echo '	RewriteEngine on';
echo '	RewriteRule ^/$ control [L,R=301]';

echo '	ErrorDocument 403 /errors/403/index.php';
echo '	ErrorDocument 404 /errors/404/index.php';
echo '	ErrorDocument 500 /errors/500/index.php';

echo "	<Directory \"$ICONF_DOCROOT\">";
echo '		Options -Indexes';
echo '	</Directory>';

# Send a 404 response when trying to access 'data/'.
echo "	<DirectoryMatch \"^$ICONF_DOCROOT(/?)$ICONF_NAME(/?)data(.+)\">";
echo '		RewriteEngine On';
echo '		RewriteRule .* - [R=404,L]';
echo '	</DirectoryMatch>';

echo '</VirtualHost>';
