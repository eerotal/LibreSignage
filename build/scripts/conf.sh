#!/bin/sh

##
##  Configuration constants for the LibreSignage build system.
##

# Path constants.
SRC_DIR='src';
DIST_DIR='dist';
CONF_DIR='server';

# Block access to these directories in the server config. This is a
# vertical bar separated list of directory paths. Note that this
# string is directly used in a regex expression, ie. ...($BLOCK_DIRS)...
BLOCKED_PATHS='data|common|config';

# LibreSignage version information.
LS_VER=`git describe --always --tags --dirty`;
API_VER=2;

if [ -z "$SUDO_USER" ]; then
	OWNER=$USER;
else
	OWNER=$SUDO_USER;
fi
