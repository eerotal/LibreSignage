#!/bin/sh

#
#  Build config defaults for apache2-debian-docker.
#

CONF_APPROOT='/var/www/html'
CONF_ADMIN_NAME='admin'
CONF_ADMIN_EMAIL='admin@example.com'

# These must be FALSE by default.
CONF_FEATURE_IMGTHUMBS='FALSE'
CONF_FEATURE_VIDTHUMBS='FALSE'
CONF_DEBUG='FALSE'
