# LibreSignage Virtual Host configuration template.
# This file is sourced in install.sh.

echo '<VirtualHost *:80>';
if [ -n "${INSTC[ADMIN_EMAIL]}" ]; then
	echo '	ServerAdmin '${INSTC[ADMIN_EMAIL]};
fi
echo '	ServerName '${INSTC[NAME]};
if [ -n "${INSTC[ALIAS]}" ]; then
	echo '	ServerAlias '${INSTC[ALIAS]};
fi
echo '	DocumentRoot '${INSTC[DOCROOT]}'/'${INSTC[NAME]};
echo '	ErrorLog ${APACHE_LOG_DIR}/error.log';
echo '	CustomLog ${APACHE_LOG_DIR}/access.log combined';

echo '	RewriteEngine on';
echo '	RewriteRule ^/$ control [L,R=301]';

echo '	ErrorDocument 403 /errors/403/index.php';
echo '	ErrorDocument 404 /errors/404/index.php';
echo '	ErrorDocument 500 /errors/500/index.php';

echo '</VirtualHost>';
