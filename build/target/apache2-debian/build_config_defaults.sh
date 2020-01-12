#!/bin/sh

#
# Build config defaults for apache2-debian
#
CONF_INSTALL_DIR='/var/www'
CONF_NAME='localhost'
CONF_ALIAS=''
CONF_ADMIN_NAME="Example Admin"
CONF_ADMIN_EMAIL="admin@example.com"

# These must be FALSE by default because they are enabled using
# simple command line options.
CONF_FEATURE_IMGTHUMBS="FALSE"
CONF_FEATURE_VIDTHUMBS="FALSE"
CONF_DEBUG="FALSE"
