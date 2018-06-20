#!/bin/bash

# Common configuration values for the LibreSignage build system.

# Path constants.
SRC_DIR='src';
DIST_DIR='dist';
APACHE_SITES='/etc/apache2/sites-available';
INSTC_FILE_EXT='.instconf';
RST_DIR="$DIST_DIR/doc/rst";
HTML_DIR="$DIST_DIR/doc/html";
CLIENT_DIR="$DIST_DIR/client";
API_DOC="$RST_DIR/api.rst";
API_ENDPOINTS_DIR="$DIST_DIR/api/endpoint";
LS_README="README.rst";

if [ -z "$SUDO_USER" ]; then
	OWNER=$USER;
else
	OWNER=$SUDO_USER;
fi
