# LibreSignage Virtual Host configuration template.
# This file is sourced in install.sh.

echo '<VirtualHost *:80>';
if [ -n "${INSTC[EMAIL]}" ]; then
	echo '	ServerAdmin '${INSTC[EMAIL]};
fi
echo '	ServerName '${INSTC[NAME]};
if [ -n "${INSTC[ALIAS]}" ]; then
	echo '	ServerAlias '${INSTC[ALIAS]};
fi
echo '	DocumentRoot '${INSTC[DOCROOT]}'/'${INSTC[NAME]};
echo '	ErrorLog ${APACHE_LOG_DIR}/error.log';
echo '	CustomLog ${APACHE_LOG_DIR}/access.log combined';
echo '</VirtualHost>';
