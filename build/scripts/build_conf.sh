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
LS_INSTANCE_CONF="common/php/ls_instance.php";

# Default file permissions.
DEF_OWNER="$USER:$USER";
DEF_MODE="755";

# File permission exceptions.
declare -A owner_data;
owner_data['data']="-R $USER:www-data";

declare -A mode_data;
mode_data['data']="-R 775";

