#!/bin/bash

# Common configuration values for the LibreSignage build system.

# Path constants.
SRC_DIR='src';
DIST_DIR='dist';
APACHE_SITES='/etc/apache2/sites-available';
INSTC_FILE_EXT='.instconf';
MD_DIR="$DIST_DIR/doc/md";
HTML_DIR="$DIST_DIR/doc/html";
API_DOC="$MD_DIR/api.md";
API_ENDPOINTS_DIR="$DIST_DIR/api/endpoint";

# Default file permissions.
DEF_OWNER="$USER:$USER";
DEF_MODE="755";

# File permission exceptions.
declare -A owner_data;
owner_data['data']="-R $USER:www-data";

declare -A mode_data;
mode_data['data']="-R 775";

