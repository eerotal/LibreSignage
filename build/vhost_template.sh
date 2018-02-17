# LibreSignage Virtual Host configuration template.
# This file is sourced in install.sh.

echo '<VirtualHost *:80>';
echo '	ServerAdmin '$SERVER_ADMIN_EMAIL;
echo '	ServerName '$SERVER_NAME;
if [ -n "$SERVER_ALIAS" ]; then
	echo '	ServerAlias '$SERVER_ALIAS;
fi
echo '	DocumentRoot '$DOCUMENT_ROOT'/'$SERVER_NAME;
echo '	ErrorLog ${APACHE_LOG_DIR}/error.log';
echo '	CustomLog ${APACHE_LOG_DIR}/access.log combined';
echo '</VirtualHost>';
