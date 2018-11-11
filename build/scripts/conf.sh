#!/bin/sh

##
##  Configuration constants for the LibreSignage build system.
##

# Path constants.
SRC_DIR='src';
DIST_DIR='dist';
CONF_DIR='server';

# LibreSignage version information.
LS_VER=`git describe --always --tags --dirty`;
API_VER=2;

if [ -z "$SUDO_USER" ]; then
	OWNER=$USER;
else
	OWNER=$SUDO_USER;
fi
