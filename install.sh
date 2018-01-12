#!/bin/sh

# A script for installing LibreSignage on a system.
# $1 is the install directory.

libresignage_install() {
	echo "Installing LibreSignage into $1..."
	cp -Rpv src/* "$1/"
	echo 'Done!'
}

if [ -z $1 ]; then
	echo 'No install directory specified. Aborting install!';
	exit 1;
fi

if [ -n "$(ls -a $1)" ]; then
	echo 'Install directory is not empty.'
	read -p 'Remove existing files and continue? (Y\N)' read_val
	case $read_val in
		[Yy]* ) rm -rf $1/*; libresignage_install $1; break;;
		* ) echo 'Aborting install!'; exit 1;;
	esac
fi
