#!/bin/sh

#
# Build configuration script for the apache2-debian-interactive target.
#

set -e
. build/scripts/conf.sh
. build/scripts/fancyread.sh
. build/target/apache2-debian/build_config_defaults.sh

CONF_INSTALL_DIR=$(fancyread "Install directory" "$CONF_INSTALL_DIR")
CONF_NAME=$(fancyread "Server domain" "$CONF_NAME")
CONF_ALIAS=$(fancyread "Domain aliases" "$CONF_ALIAS")
CONF_ADMIN_NAME=$(fancyread "Admin name" "$CONF_ADMIN_NAME")
CONF_ADMIN_EMAIL=$(fancyread "Admin email" "$CONF_ADMIN_EMAIL")

imgthumbs=$(fancyread \
	"Enable image thumbnail generation?"\
	$(\
		if [ "$CONF_FEATURE_IMGTHUMBS" = "TRUE" ]; then\
			echo 'Y';\
		else\
			echo "N";\
		fi\
	)\
	Y N y n
)
vidthumbs=$(fancyread \
	"Enable video thumbnail generation?"\
	$(\
		if [ "$CONF_FEATURE_VIDTHUMBS" = "TRUE" ]; then\
			echo 'Y';\
		else\
			echo "N";\
		fi\
	)\
	Y N y n
)
debug=$(fancyread \
	"Enable debugging?"\
	$(\
		if [ "$CONF_DEBUG" = "TRUE" ]; then\
			echo 'Y';\
		else\
			echo "N";\
		fi\
	)\
	Y N y n
)

echo \
	"--install-dir='$CONF_INSTALL_DIR' "\
	"--server-name='$CONF_NAME' "\
	"--server-aliases='$CONF_ALIAS' "\
	"--admin-name='$CONF_ADMIN_NAME' "\
	"--admin-email='$CONF_ADMIN_EMAIL' "\
	$(case "$imgthumbs" in [yY]*) echo '--feature-imgthumbs '; esac)\
	$(case "$vidthumbs" in [yY]*) echo '--feature-vidthumbs '; esac)\
	$(case "$debug" in [yY]*) echo '--debug '; esac)\
| xargs './build/target/apache2-debian/build_config.sh'
